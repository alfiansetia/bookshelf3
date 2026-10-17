---
paths:
    - 'app/**'
---

# App

## Domain vocabulary lives in enums and config/bookshelf.php

Book statuses, shelf themes, and social platforms are PHP enums at app/BookStatus.php, app/ShelfTheme.php, app/SocialPlatform.php; each exposes label()/description()/color()/icon() plus a static options() array for the frontend. Lists and limits (languages, username rules, upload caps, pagination) come from config/bookshelf.php only - do not duplicate them in controllers or JS.

## Uploaded images go through one action and are stored as WebP

Every image upload (book cover and avatar) goes through app/Actions/StoreUploadedImage.php keyed by app/UploadedImageType.php: the image is oriented from EXIF, scaled down to the limits in config/bookshelf.php, and re-encoded to WebP. Never write an uploaded image straight to a disk elsewhere, never assume the stored file keeps the extension of the upload, and keep the source dimension caps in app/Concerns/BookValidationRules.php and app/Concerns/ProfileValidationRules.php in sync with that config.

## Authentication is Google-only, never a password

Users are created and signed in only through Google: app/Http/Controllers/Auth/GoogleAuthController.php plus Laravel Socialite. The app stores no password at all - no `password` column, no password reset, no 2FA, no passkey, no email verification gate. Never add password fields, `Hash::make`, `Auth::attempt`, or Fortify features/views. The login/register pages are `Route::inertia` routes in routes/web.php and `POST /login` only redirects to Google. Logout uses app/Http/Controllers/Auth/LogoutController.php. `users.google_id` is required and unique, and `email_verified_at` is filled automatically from Google (app/**).
