# Hostinger Deployment Addendum — Phase 22

## Phase 22

Flutter Mobile App Foundation.

React Native and Expo were not used because this project mobile stack is Flutter.

## Database migration

Run after Phase 21:

```text
backend/php/database/migrations/022_flutter_mobile_app_foundation.sql
```

Export a database backup first.

## Backend files to upload

```text
backend/php/shared/MobileApi.php
backend/php/database/migrations/022_flutter_mobile_app_foundation.sql
```

## API files to upload

```text
web/public/api/mobile/login/index.php
web/public/api/mobile/me/index.php
web/public/api/mobile/logout/index.php
web/public/api/mobile/dashboard/index.php
```

## Flutter project

Flutter source is here:

```text
mobile/flutter_app
```

Do not upload Flutter source as PHP pages inside public_html. Use it for local app development or CI builds.

## API test

Login:

```text
POST /api/mobile/login
```

Then use:

```text
Authorization: Bearer TOKEN
```

Test:

```text
GET /api/mobile/me
GET /api/mobile/dashboard
POST /api/mobile/logout
```

## Run Flutter locally

```bash
cd mobile/flutter_app
flutter pub get
flutter run --dart-define=BACKEND_BASE_URL=https://staging.mshabibanabil.com
```

## Firebase push

Add Firebase config locally only:

```text
android/app/google-services.json
ios/Runner/GoogleService-Info.plist
```

Do not commit Firebase config/secrets.

## Manual test

```text
Run migration 022
Test mobile login API
Test mobile dashboard API as Owner
Run Flutter app
Login as Owner
Logout
Login as Student
Configure Firebase locally
Confirm push token registration
```

## Known limitations

```text
This is a mobile foundation, not the full production app yet.
Scenario recording is not implemented yet.
Attendance/no-show actions are not implemented yet.
WhatsApp quick actions are not implemented yet.
Push tap routing is not implemented yet.
Parent booking/reschedule is not implemented yet.
```

Stop here. Test Phase 22 before continuing.
