<?php
/**
 * Phase 6A V2 importer for the free public level test bank.
 *
 * Reads the text files under:
 * phases/files needed/Level Test
 *
 * Imports:
 * - Reading A2/B1/B2/C1/C2 files into free_level_test_reading_texts/questions.
 * - Listening A2/B1/B2/C1/C2 files into free_level_test_listening_scripts/questions.
 *
 * Safe to run multiple times. It removes previously imported rows for the same
 * source notes before inserting fresh data.
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/FreeLevelTest.php';

const SOURCE_NOTE = 'imported_from_phases_files_needed_level_test';

function bank_base_path(): string
{
    return dirname(__DIR__, 4) . '/phases/files needed/Level Test';
}

function normalize_text_content(string $content): string
{
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    $content = str_replace("```", '', $content);
    return trim($content);
}

function parse_header_meta(string $header): array
{
    $dialect = null;
    $topic = null;

    if (preg_match('/\((.*?)\)/u', $header, $m)) {
        $inside = trim($m[1]);
        $parts = preg_split('/\s*[–-]\s*/u', $inside, 2);
        $dialect = trim($parts[0] ?? '');
        $topic = trim($parts[1] ?? '');
    }

    return [$dialect ?: null, $topic ?: null];
}

function parse_answers_line(string $answersLine): array
{
    $answers = [];
    if (preg_match_all('/(\d+)\s*[-–]\s*([A-D])/u', $answersLine, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $answers[(int) $match[1]] = $match[2];
        }
    }
    return $answers;
}

function parse_options_line(string $line): array
{
    $options = [];
    if (preg_match_all('/([A-D])\)\s*(.*?)(?=\s+[A-D]\)|$)/u', $line, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $options[$match[1]] = trim($match[2]);
        }
    }
    return $options;
}

function parse_mcq_questions(string $body, int $expectedCount): array
{
    $lines = array_values(array_filter(array_map('trim', explode("\n", $body)), fn($line) => $line !== ''));
    $answers = [];

    foreach ($lines as $line) {
        if (str_contains($line, 'Answers')) {
            $answers = parse_answers_line($line);
            break;
        }
    }

    $questions = [];
    for ($i = 0; $i < count($lines); $i++) {
        if (preg_match('/^(\d+)\.\s*(.+)$/u', $lines[$i], $qMatch)) {
            $number = (int) $qMatch[1];
            $questionText = trim($qMatch[2]);
            $optionLine = $lines[$i + 1] ?? '';
            $options = parse_options_line($optionLine);

            if (count($options) < 4) {
                continue;
            }

            $questions[] = [
                'number' => $number,
                'question_text' => $questionText,
                'option_a' => $options['A'] ?? '',
                'option_b' => $options['B'] ?? '',
                'option_c' => $options['C'] ?? '',
                'option_d' => $options['D'] ?? '',
                'correct_option' => $answers[$number] ?? 'A',
                'points' => 1,
            ];
        }
    }

    if (count($questions) !== $expectedCount) {
        echo "Warning: expected {$expectedCount} questions but parsed " . count($questions) . "\n";
    }

    return $questions;
}

function delete_previous_imports(): void
{
    $pdo = db();

    $readingIds = $pdo->query('SELECT id FROM free_level_test_reading_texts WHERE notes = ' . $pdo->quote(SOURCE_NOTE))->fetchAll(PDO::FETCH_COLUMN);
    if ($readingIds) {
        $in = implode(',', array_map('intval', $readingIds));
        $pdo->exec("DELETE FROM free_level_test_reading_questions WHERE reading_text_id IN ({$in})");
        $pdo->exec("DELETE FROM free_level_test_reading_texts WHERE id IN ({$in})");
    }

    $scriptIds = $pdo->query('SELECT id FROM free_level_test_listening_scripts WHERE notes = ' . $pdo->quote(SOURCE_NOTE))->fetchAll(PDO::FETCH_COLUMN);
    if ($scriptIds) {
        $in = implode(',', array_map('intval', $scriptIds));
        $pdo->exec("DELETE FROM free_level_test_listening_questions WHERE script_id IN ({$in})");
        $pdo->exec("DELETE FROM free_level_test_listening_scripts WHERE id IN ({$in})");
    }
}

function import_reading_file(string $path, string $level): int
{
    $content = normalize_text_content(file_get_contents($path));
    $parts = preg_split('/═{10,}/u', $content);
    $imported = 0;

    foreach ($parts as $index => $part) {
        $part = trim($part);
        if (!preg_match('/📘\s*Text\s+(\d+)\s*(.*?)\n(.+)/us', $part, $match)) {
            continue;
        }

        $textNumber = (int) $match[1];
        $header = trim($match[2]);
        $body = trim($match[3]);
        [$dialect, $topic] = parse_header_meta($header);

        $passage = '';
        if (preg_match('/"(.+?)"/us', $body, $passageMatch)) {
            $passage = trim($passageMatch[1]);
        }

        if ($passage === '') {
            echo "Skipping reading {$level} text {$textNumber}: missing passage.\n";
            continue;
        }

        $questions = parse_mcq_questions($body, 5);
        if (!$questions) {
            echo "Skipping reading {$level} text {$textNumber}: no questions.\n";
            continue;
        }

        db()->prepare('INSERT INTO free_level_test_reading_texts (bank_type, level, text_number, title, passage_text, topic, dialect_style, notes, is_active) VALUES ("shared", :level, :num, :title, :passage, :topic, :dialect, :notes, 1)')
            ->execute([
                ':level' => $level,
                ':num' => $textNumber,
                ':title' => "{$level} Reading Text {$textNumber}" . ($topic ? " - {$topic}" : ''),
                ':passage' => $passage,
                ':topic' => $topic,
                ':dialect' => $dialect,
                ':notes' => SOURCE_NOTE,
            ]);
        $textId = (int) db()->lastInsertId();

        foreach ($questions as $q) {
            db()->prepare('INSERT INTO free_level_test_reading_questions (reading_text_id, question_text, option_a, option_b, option_c, option_d, correct_option, points, sort_order, is_active) VALUES (:text_id, :question, :a, :b, :c, :d, :correct, 1, :sort, 1)')
                ->execute([
                    ':text_id' => $textId,
                    ':question' => $q['question_text'],
                    ':a' => $q['option_a'],
                    ':b' => $q['option_b'],
                    ':c' => $q['option_c'],
                    ':d' => $q['option_d'],
                    ':correct' => $q['correct_option'],
                    ':sort' => $q['number'],
                ]);
        }

        $imported++;
    }

    return $imported;
}

function import_listening_file(string $path, string $level): int
{
    $content = normalize_text_content(file_get_contents($path));
    $parts = preg_split('/═{10,}/u', $content);
    $imported = 0;

    foreach ($parts as $part) {
        $part = trim($part);
        if (!preg_match('/🎧\s*Script\s+(\d+)\s*(.*?)\n(.+)/us', $part, $match)) {
            continue;
        }

        $scriptNumber = (int) $match[1];
        $header = trim($match[2]);
        $body = trim($match[3]);
        [$dialect, $topic] = parse_header_meta($header);

        $scriptText = '';
        if (preg_match('/"(.+?)"/us', $body, $scriptMatch)) {
            $scriptText = trim($scriptMatch[1]);
        }

        $questions = parse_mcq_questions($body, 3);
        if (!$questions) {
            echo "Skipping listening {$level} script {$scriptNumber}: no questions.\n";
            continue;
        }

        $lower = strtolower($level);
        $audioUrl = "/assets/audio/level-test/listening/{$lower}/{$lower}_{$scriptNumber}.mp3";

        db()->prepare('INSERT INTO free_level_test_listening_scripts (level, script_number, audio_url, title, topic, dialect_style, script_text, notes, is_active) VALUES (:level, :num, :url, :title, :topic, :dialect, :script_text, :notes, 1)')
            ->execute([
                ':level' => $level,
                ':num' => $scriptNumber,
                ':url' => $audioUrl,
                ':title' => "{$level} Listening Script {$scriptNumber}" . ($topic ? " - {$topic}" : ''),
                ':topic' => $topic,
                ':dialect' => $dialect,
                ':script_text' => $scriptText,
                ':notes' => SOURCE_NOTE,
            ]);
        $scriptId = (int) db()->lastInsertId();

        foreach ($questions as $q) {
            db()->prepare('INSERT INTO free_level_test_listening_questions (script_id, question_text, option_a, option_b, option_c, option_d, correct_option, points, sort_order, is_active) VALUES (:script_id, :question, :a, :b, :c, :d, :correct, 1, :sort, 1)')
                ->execute([
                    ':script_id' => $scriptId,
                    ':question' => $q['question_text'],
                    ':a' => $q['option_a'],
                    ':b' => $q['option_b'],
                    ':c' => $q['option_c'],
                    ':d' => $q['option_d'],
                    ':correct' => $q['correct_option'],
                    ':sort' => $q['number'],
                ]);
        }

        $imported++;
    }

    return $imported;
}

function import_level_test_bank(): void
{
    flt_seed_defaults();
    delete_previous_imports();

    $base = bank_base_path();
    $readingFiles = [
        'A2' => $base . '/Reading A2 .txt',
        'B1' => $base . '/Reading B1.txt',
        'B2' => $base . '/Reading B2.txt',
        'C1' => $base . '/Reading C1.txt',
        'C2' => $base . '/Reading C2.txt',
    ];

    $listeningFiles = [
        'A2' => $base . '/الاستماع/a2/A2.txt',
        'B1' => $base . '/الاستماع/b1/B1.txt',
        'B2' => $base . '/الاستماع/b2/B2.txt',
        'C1' => $base . '/الاستماع/c1/C1.txt',
        'C2' => $base . '/الاستماع/c2/C2.txt',
    ];

    $readingTotal = 0;
    foreach ($readingFiles as $level => $file) {
        if (!file_exists($file)) {
            echo "Missing reading file: {$file}\n";
            continue;
        }
        $count = import_reading_file($file, $level);
        $readingTotal += $count;
        echo "Imported {$count} reading texts for {$level}.\n";
    }

    $listeningTotal = 0;
    foreach ($listeningFiles as $level => $file) {
        if (!file_exists($file)) {
            echo "Missing listening file: {$file}\n";
            continue;
        }
        $count = import_listening_file($file, $level);
        $listeningTotal += $count;
        echo "Imported {$count} listening scripts for {$level}.\n";
    }

    echo "Done. Imported {$readingTotal} reading texts and {$listeningTotal} listening scripts.\n";
}

import_level_test_bank();
