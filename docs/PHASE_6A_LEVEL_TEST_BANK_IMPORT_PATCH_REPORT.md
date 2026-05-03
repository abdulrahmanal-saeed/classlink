# Phase 6A Level Test Bank Import Patch Report
# تقرير Patch استيراد بنك اختبار المستوى

## What was added / ما الذي تم إضافته

Added an importer that reads the files from:

```text
phases/files needed/Level Test
```

and imports them into the Phase 6A free public level test bank tables.

تم إضافة سكريبت import يقرأ ملفات بنك اختبار المستوى من فولدر:

```text
phases/files needed/Level Test
```

ويدخلها في جداول اختبار المستوى المجاني الخاصة بـ Phase 6A.

---

## New File / الملف الجديد

```text
backend/php/database/seeds/import_free_level_test_bank.php
```

---

## Source Files Used / الملفات المصدر المستخدمة

### Reading

```text
phases/files needed/Level Test/Reading A2 .txt
phases/files needed/Level Test/Reading B1.txt
phases/files needed/Level Test/Reading B2.txt
phases/files needed/Level Test/Reading C1.txt
phases/files needed/Level Test/Reading C2.txt
```

### Listening

```text
phases/files needed/Level Test/الاستماع/a2/A2.txt
phases/files needed/Level Test/الاستماع/b1/B1.txt
phases/files needed/Level Test/الاستماع/b2/B2.txt
phases/files needed/Level Test/الاستماع/c1/C1.txt
phases/files needed/Level Test/الاستماع/c2/C2.txt
```

---

## What the Importer Parses / ما الذي يقرأه السكريبت

### Reading files

For each reading text, the importer extracts:

```text
level
text_number
title
dialect_style
topic
passage_text
5 MCQ questions
A/B/C/D options
correct answers
```

Data goes into:

```text
free_level_test_reading_texts
free_level_test_reading_questions
```

Reading rows use:

```text
bank_type = shared
notes = imported_from_phases_files_needed_level_test
```

This allows the same reading bank to be used by Quick Check and Full Test.

---

### Listening files

For each listening script, the importer extracts:

```text
level
script_number
title
dialect_style
topic
script_text
3 MCQ questions
A/B/C/D options
correct answers
```

Data goes into:

```text
free_level_test_listening_scripts
free_level_test_listening_questions
```

Listening rows use:

```text
notes = imported_from_phases_files_needed_level_test
```

---

## Audio URL Format / صيغة روابط الصوت

The importer stores audio URLs using the required format:

```text
/assets/audio/level-test/listening/{level}/{level}_{scriptNumber}.mp3
```

Examples:

```text
/assets/audio/level-test/listening/a2/a2_1.mp3
/assets/audio/level-test/listening/b1/b1_10.mp3
/assets/audio/level-test/listening/c2/c2_10.mp3
```

A2 supports only:

```text
a2_1.mp3 to a2_9.mp3
```

No leading zero is used.

---

## Important Deployment Note / ملاحظة مهمة عند الرفع

The importer imports the listening metadata and questions only.

السكريبت يستورد بيانات الاستماع والأسئلة فقط.

You must make sure the actual audio files exist under:

```text
web/public/assets/audio/level-test/listening/a2/
web/public/assets/audio/level-test/listening/b1/
web/public/assets/audio/level-test/listening/b2/
web/public/assets/audio/level-test/listening/c1/
web/public/assets/audio/level-test/listening/c2/
```

Example physical file:

```text
web/public/assets/audio/level-test/listening/a2/a2_1.mp3
```

If the mp3 files are not present, the database import will still work, but the audio player will not play sound.

---

## Safe Re-run Behavior / قابلية التشغيل أكثر من مرة

The importer is safe to run multiple times.

It removes rows previously imported with:

```text
notes = imported_from_phases_files_needed_level_test
```

Then inserts the latest content again.

It does not delete manually added Owner bank items unless they use the same notes marker.

---

## How to Run / طريقة التشغيل

After applying Phase 6A migration:

```text
backend/php/database/migrations/006a_free_public_level_test_foundation.sql
```

Run:

```bash
php backend/php/database/seeds/import_free_level_test_bank.php
```

Expected output:

```text
Imported 5 reading texts for A2.
Imported 5 reading texts for B1.
Imported 5 reading texts for B2.
Imported 5 reading texts for C1.
Imported 5 reading texts for C2.
Imported 9 listening scripts for A2.
Imported 10 listening scripts for B1.
Imported 10 listening scripts for B2.
Imported 10 listening scripts for C1.
Imported 10 listening scripts for C2.
Done.
```

Counts may vary if a source file changes.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Run Phase 6A migration.
2. Run the importer:

```bash
php backend/php/database/seeds/import_free_level_test_bank.php
```

3. Check reading tables:

```text
free_level_test_reading_texts
free_level_test_reading_questions
```

4. Check listening tables:

```text
free_level_test_listening_scripts
free_level_test_listening_questions
```

5. Open Owner settings:

```text
/owner/free-level-test/settings
```

6. Confirm audio availability shows:

```text
A2: 9 available
B1: 10 available
B2: 10 available
C1: 10 available
C2: 10 available
```

7. Open Quick Check:

```text
/level-test/quick
```

8. Confirm it uses real imported reading texts.
9. Open Full Test:

```text
/level-test/entry
```

10. Start a full test and confirm listening scripts show audio player and imported questions.

---

## Stop Point / نقطة التوقف

Stop here. Import the bank and test before continuing.

توقف هنا. شغّل الاستيراد واختبر البنك قبل الانتقال للمرحلة التالية.
