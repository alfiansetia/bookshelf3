---
paths:
    - 'resources/js/**'
---

# Js

## Call routes through Wayfinder helpers

Never hardcode URLs. Import generated helpers from `@/routes/**` (named routes) or `@/actions/**` (controllers) and call `.url()`/`.form()` on the returned definition, e.g. `route.url()`, `edit(book.id)`, `update.form()`. Run `php artisan wayfinder:generate --with-form` after adding or renaming routes, otherwise the generated helpers are stale.

## Vue and TypeScript house style

Single quotes, semicolons, 4-space indentation, print width 80, one root element per component, `<script setup lang="ts">` with an explicit `<script setup>` block first. Use the `cn()` helper for conditional classes and keep Tailwind classes literal so the compiler can see them. Run `npx vp check --fix` for lint/format and `npx vue-tsc --noEmit` for types before finishing.

## All user-facing copy is Bahasa Indonesia

Copy uses informal second person ("kamu"), sentence case, and short warm sentences without marketing hype. Numbers and dates go through the helpers in resources/js/lib/format.ts (id-ID locale, `formatNumber`, `formatDate`, `bookCount`). Keep empty states actionable and avoid exclamation marks.
