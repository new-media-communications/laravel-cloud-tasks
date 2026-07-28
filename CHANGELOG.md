# Changelog

All notable changes to `laravel-cloud-tasks` will be documented in this file.

## 3.0.0 - 2026-07-28

- Added an email allowlist (`auth.allowed_emails` / `CLOUD_TASKS_AUTH_ALLOWED_EMAILS`) so the handler only accepts tasks signed by approved service accounts
- Added `auth.enabled` (`CLOUD_TASKS_AUTH_ENABLED`) to toggle OIDC signing and verification
- Service account email and audience now fall back to the Application Default Credentials identity and the handler URL when not explicitly configured
- Verify OIDC tokens against Google's public keys instead of the local service-account credentials
- **BREAKING:** authentication is now enabled by default. Set `CLOUD_TASKS_AUTH_ENABLED=false` to keep the handler unauthenticated, or ensure Application Default Credentials are available so an identity can be resolved on both ends

## 2.0.0 - 2026-03-19

- Added Laravel 13 support
- Dropped Laravel 9 and 10 support
- Dropped PHP 8.1 and 8.2 support

## 1.2.2 - 2026-01-07

- Fixed Google Cloud Tasks v2 API compatibility for queue operations

## 1.2.1 - 2026-01-06

- Upgraded google/cloud-tasks to ^2.0

## 1.2.0 - 2025-07-16

- Added Laravel 12 support
- Added PHP 8.4 support

## 1.0.2 - 2024-09-25

- Added Laravel 11 support

## 1.0.1 - 2024-02-06

- Added PHP 8.3 support

## 1.0.0 - 2023-05-23

- Initial release
