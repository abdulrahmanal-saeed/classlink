<?php
/**
 * Phase 30 Sales Funnel Copywriting helper.
 * Central reusable copy blocks for public pages, checkout, onboarding, and communications.
 */

function sales_funnel_ctas(): array
{
    return [
        'primary' => 'Start Now — Pay Securely',
        'book_lesson' => 'Book Your First Arabic Lesson',
        'level_check' => 'Start with a Personalized Level Check',
        'child' => 'Help Your Child Read Arabic with Confidence',
        'conversation' => 'Learn Arabic for Real Conversations',
    ];
}

function sales_funnel_faq(): array
{
    return [
        'I don’t know any Arabic. Can I still start?' => 'Yes. You do not need to know Arabic before starting. The first step is to understand your real level and choose the right learning path for you.',
        'I understand Arabic but cannot speak. Is this for me?' => 'Yes. Many learners understand Arabic but freeze when speaking. Lessons focus on real conversations, sentence building, and safe speaking practice.',
        'My child speaks Arabic but cannot read or write. Can you help?' => 'Yes. Child learners can follow a literacy path focused on letters, reading, writing, connection between letters, and confidence with simple Arabic texts.',
        'I’m afraid of making mistakes.' => 'Mistakes are part of the lesson, not a problem. You get gentle correction, useful phrases, and repeated speaking practice until the language feels easier.',
        'I need Arabic for work in the UAE or Gulf. Is the course practical?' => 'Yes. Lessons can focus on workplace Arabic, daily communication, customer conversations, and the difference between Modern Standard Arabic and local dialect when needed.',
        'Do I need MSA or dialect?' => 'You do not need to decide alone. Your teacher helps you choose the best mix based on your goal: speaking, work, reading/writing, Emirati dialect, Egyptian dialect, or general Arabic.',
        'What happens after payment?' => 'You complete a student form, take a level check if needed, choose a lesson time, and then your teacher prepares a personalized first lesson.',
        'Can I reschedule?' => 'Yes, rescheduling follows the academy cancellation policy. The booking system and teacher confirmation help keep lessons organized.',
        'Will lessons be personalized?' => 'Yes. Lessons are planned around your level, goal, age, learning style, homework, mistakes, and progress.',
    ];
}

function sales_funnel_whatsapp_templates(): array
{
    return [
        'after_payment' => 'Hi [Name], thank you for booking with Habiba Nabil Arabic Academy 🎉 Your next step is to complete the student form so we can prepare the right Arabic learning path for you.',
        'level_check_received' => 'Hi [Name], we received your level check. Your writing/speaking parts may need teacher review. We will guide you to the next step soon.',
        'lesson_confirmation' => 'Hi [Name], your Arabic lesson is confirmed for [Lesson Date]. Please join from a quiet place and bring any questions you want to practice.',
        'homework_published' => 'Hi [Name], new Arabic practice is ready for you. Open your dashboard and complete it before your next lesson.',
    ];
}
