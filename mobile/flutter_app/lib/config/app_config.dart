class AppConfig {
  // Change this for local/staging/production builds.
  static const String backendBaseUrl = String.fromEnvironment(
    'BACKEND_BASE_URL',
    defaultValue: 'https://staging.mshabibanabil.com',
  );
}
