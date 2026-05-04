# Phase 22 Execution Report
# تقرير تنفيذ المرحلة 22

## Phase Name / اسم المرحلة

Flutter Mobile App Foundation

أساس تطبيق الموبايل Flutter

---

## Important Stack Decision / قرار مهم بخصوص الـ Stack

The phase prompt mentioned React Native / Expo.

However, the agreed project stack is:

```text
Backend: PHP + MySQL
Mobile app: Flutter
Firebase: supporting services where needed
```

Therefore, React Native / Expo was rejected and Phase 22 was implemented as Flutter.

---

## Goal / الهدف

Build a mobile app foundation connected to the same backend.

بناء أساس تطبيق موبايل Flutter متصل بنفس Backend.

The web platform remains the source of truth.

---

## Database Migration / تحديث قاعدة البيانات

Phase 22 adds:

```text
backend/php/database/migrations/022_flutter_mobile_app_foundation.sql
```

---

## Files Created / الملفات التي تم إنشاؤها

### Database

```text
backend/php/database/migrations/022_flutter_mobile_app_foundation.sql
```

### Backend helper

```text
backend/php/shared/MobileApi.php
```

### Mobile API endpoints

```text
web/public/api/mobile/login/index.php
web/public/api/mobile/me/index.php
web/public/api/mobile/logout/index.php
web/public/api/mobile/dashboard/index.php
```

### Flutter app

```text
mobile/flutter_app/pubspec.yaml
mobile/flutter_app/lib/main.dart
mobile/flutter_app/lib/config/app_config.dart
mobile/flutter_app/lib/services/api_service.dart
mobile/flutter_app/lib/services/auth_service.dart
mobile/flutter_app/lib/services/push_service.dart
mobile/flutter_app/lib/widgets/dashboard_card.dart
mobile/flutter_app/lib/screens/login_screen.dart
mobile/flutter_app/lib/screens/role_home_screen.dart
mobile/flutter_app/lib/screens/owner_dashboard_screen.dart
mobile/flutter_app/lib/screens/student_dashboard_screen.dart
mobile/flutter_app/lib/screens/parent_dashboard_screen.dart
```

---

## Files Changed / الملفات التي تم تعديلها

```text
mobile/flutter_app/README.md
```

---

## Migration 022 Changes / تغييرات Migration 022

### New table: mobile_auth_tokens

Bearer-token auth for Flutter mobile app:

```text
user_id
token_hash
device_label
platform: flutter_android / flutter_ios / flutter_web / unknown
status: active / revoked / expired
expires_at
last_used_at
created_at
revoked_at
```

Tokens are stored hashed in database.

---

### Mobile settings inserted

```text
mobile_api_enabled = 1
mobile_token_ttl_days = 30
mobile_app_min_supported_version = 1.0.0
mobile_app_backend_base_url = https://staging.mshabibanabil.com
```

---

## Backend Mobile API / API الموبايل

Implemented:

```text
POST /api/mobile/login
GET /api/mobile/me
POST /api/mobile/logout
GET /api/mobile/dashboard
```

Uses:

```text
Authorization: Bearer MOBILE_TOKEN
```

The mobile API uses the same users table and role model as the web platform.

---

## Mobile Login / تسجيل الدخول للموبايل

`POST /api/mobile/login`

Request:

```json
{
  "email": "owner@demo.com",
  "password": "demo password",
  "platform": "flutter_android",
  "device_label": "Flutter app"
}
```

Response:

```json
{
  "ok": true,
  "token": "...",
  "user": {
    "id": 1,
    "email": "owner@demo.com",
    "role": "owner_teacher",
    "display_name": "Owner"
  }
}
```

---

## Mobile Dashboard / لوحة الموبايل

`GET /api/mobile/dashboard`

Role-based response:

### Owner/Teacher

```text
Today lessons
Pending homework submissions count
Pending scenario submissions count
```

### Student

```text
Upcoming lesson
Package balance
Published homework list
```

### Parent

```text
Linked children list
```

---

## Flutter App / تطبيق Flutter

Created at:

```text
mobile/flutter_app
```

Implemented:

```text
Login screen
Role home router
Owner dashboard foundation
Student dashboard foundation
Parent dashboard foundation
API service
Auth service with shared_preferences token storage
Push service using Firebase Messaging token registration foundation
```

---

## Push Foundation / أساس Push

Flutter app includes:

```text
firebase_core
firebase_messaging
```

`PushService` calls:

```text
POST /api/push/register-device
```

This reuses Phase 20 push backend.

Firebase config files are intentionally not committed.

Required local files later:

```text
android/app/google-services.json
ios/Runner/GoogleService-Info.plist
```

---

## Run Flutter App / تشغيل التطبيق

```bash
cd mobile/flutter_app
flutter pub get
flutter run --dart-define=BACKEND_BASE_URL=https://staging.mshabibanabil.com
```

---

## Acceptance Criteria Status / حالة القبول

Implemented:

```text
Mobile app runs in Flutter project structure
Owner can log in through mobile API
Owner dashboard API returns today lessons
Student can log in through mobile API
Student dashboard API returns upcoming lesson, balance, homework
Push token registration foundation exists
```

Partially implemented / foundation only:

```text
Owner notification open handling
Student scenario recording
WhatsApp quick actions
Add lesson notes
Mark attendance/no-show
Parent booking/reschedule
```

These require deeper mobile pages and APIs in later phases.

---

## Security / الأمان

Implemented:

```text
Mobile token stored hashed in database
Mobile logout revokes token
Mobile API can be disabled from settings
Web sessions remain unchanged
No Firebase config secrets committed
Business logic remains on backend
```

---

## Known Limitations / القيود الحالية

- This is Flutter foundation, not a full production mobile app yet.
- Firebase platform config files are not included.
- Scenario recording UI is not implemented yet.
- Attendance/no-show mobile actions are not implemented yet.
- Push notification tap routing is not implemented yet.
- Some dashboard counts depend on exact submission statuses in live database.
- API endpoints are MVP and should be expanded with role-specific ownership checks for every new mobile feature.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Run migration:

```text
backend/php/database/migrations/022_flutter_mobile_app_foundation.sql
```

2. Test mobile login API:

```text
POST /api/mobile/login
```

3. Copy returned token.
4. Test:

```text
GET /api/mobile/me
GET /api/mobile/dashboard
```

using:

```text
Authorization: Bearer TOKEN
```

5. Open Flutter project:

```bash
cd mobile/flutter_app
flutter pub get
flutter run --dart-define=BACKEND_BASE_URL=https://staging.mshabibanabil.com
```

6. Login as Owner.
7. Confirm Owner dashboard loads.
8. Logout.
9. Login as Student.
10. Confirm Student dashboard loads.
11. Configure Firebase locally.
12. Confirm push token registration hits:

```text
POST /api/push/register-device
```

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
