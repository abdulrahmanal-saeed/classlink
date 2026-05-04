# Localization QA Checklist
# قائمة مراجعة التعريب واللغة

## Goal

Make the platform professionally bilingual in Arabic and English with correct RTL/LTR behavior.

---

## Arabic copy rules

Use neutral, warm, professional Arabic.

Avoid exclusively feminine forms:

```text
اكتبي
سجّلي
اختاري
أكملي
ارفعي
```

Use neutral forms:

```text
اكتب
سجّل
اختر
أكمل
ارفع
```

Avoid robotic literal translation.

Use short, clear CTAs:

```text
ابدأ الآن
احجز حصة
أكمل النموذج
افتح الواجب
اعرض النتيجة
راجع الإجابة
```

---

## English copy rules

Use natural SaaS-style English.

Good examples:

```text
Start Now
Book a Session
Complete Student Form
View Result
Open Homework
Send Reminder
Save Changes
```

Avoid long paragraphs inside forms.

---

## RTL/LTR layout checks

Check in Arabic:

```text
HTML dir is rtl
Bootstrap RTL loads
Forms align right
Tables do not break
Dropdown menus align correctly
Email, URL, numbers, and codes remain LTR
Arabic and English mixed text does not overlap
Mobile layout is readable
```

Check in English:

```text
HTML dir is ltr
Forms align left
Tables remain normal
Cards and buttons align correctly
```

---

## Pages to audit

Public:

```text
/
/pricing
/checkout
/thank-you
/articles
/videos
/testimonials
/submit-testimonial
```

Onboarding:

```text
/student-form
/level-check-intro
/level-check
/level-test
/level-test/quick
```

Owner:

```text
/owner/dashboard
/owner/settings
/owner/payments
/owner/onboarding
/owner/calendar
/owner/homework
/owner/scenarios
/owner/reviews
/owner/materials
/owner/ai
/owner/communication
/owner/analytics
/owner/jobs
```

Student:

```text
/student/dashboard
/student/profile
/student/lessons
/student/balance
/student/homework
/student/scenarios
/student/reviews
/student/materials
/student/flashcards
/student/notifications
/student/referrals
```

Parent:

```text
/parent/dashboard
/parent/children
/parent/book
/parent/notifications
/parent/referrals
```

Academy:

```text
/academy/dashboard
/academy/briefs
/academy/briefs/new
```

Templates:

```text
Email templates
WhatsApp templates
Internal notifications
Push notification titles
AI prompts and generated drafts
```

---

## QA result fields

For each page, record:

```text
Page URL
Arabic status: pass / needs copy / layout issue
English status: pass / needs copy / layout issue
RTL issue details
Copy issue details
Priority: high / medium / low
Fixed in commit
```

---

## Stop rule

Do not mark localization complete until major flows pass in both Arabic and English on desktop and mobile.
