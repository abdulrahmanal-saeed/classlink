# Habiba Nabil Mobile App

Flutter mobile app connected to the existing PHP and MySQL backend.

## Stack

```text
Backend: PHP + MySQL
Mobile: Flutter
Firebase: support services where needed
```

React Native and Expo are not used in this project because the agreed mobile stack is Flutter.

## Phase 22 scope

```text
Login
Role-based routing
Owner dashboard foundation
Student dashboard foundation
Parent dashboard foundation
API client
Mobile bearer token storage
Firebase Messaging token registration foundation
```

## Run

```bash
flutter pub get
flutter run --dart-define=BACKEND_BASE_URL=https://staging.mshabibanabil.com
```

## Firebase push setup

Add the Firebase config files locally. Do not commit secrets.

```text
android/app/google-services.json
ios/Runner/GoogleService-Info.plist
```

## APIs used

```text
POST /api/mobile/login
GET /api/mobile/me
POST /api/mobile/logout
GET /api/mobile/dashboard
POST /api/push/register-device
```

## Manual test

```text
Run Flutter app
Login as Owner
Open dashboard
Logout
Login as Student
Open dashboard
Configure Firebase
Confirm push token registration
```
