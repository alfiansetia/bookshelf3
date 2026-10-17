---
paths:
    - 'resources/js/pages/**'
---

# Pages

## Pages get their layout from app.ts, not from imports

resources/js/app.ts resolves the layout by page-name prefix: Welcome/Explore/public/* -> PublicLayout, auth/* and onboarding/* -> AuthLayout, settings/* -> [AppLayout, SettingsLayout], everything else -> AppLayout. Pages pass layout props (breadcrumbs, title, description) through `defineOptions({ layout: { ... } })` and never wrap themselves in a layout component.

## Auth pages are Google-only

resources/js/pages/auth/Login.vue and resources/js/pages/auth/Register.vue only render a link to the Google redirect (`@/routes/auth/google`) plus Indonesian copy explaining that Rak Buku stores no password. They receive no props. Do not add email/password/OTP inputs, `PasswordInput`, passkey components, or Fortify form helpers to auth pages (resources/js/pages/**).
