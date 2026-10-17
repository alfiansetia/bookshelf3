---
paths:
    - 'tests/**'
---

# Tests

## Feature tests cover behaviour, not markup

Feature tests are Pest files in tests/Feature created with `php artisan make:test --pest {Name}` (no Feature/ prefix in the name). Use model factories and their states (finished(), indonesian(), withoutUsername(), privateShelf()) instead of hand-built models, and assert Inertia props with AssertableInertia. Run the narrowest test file you touched with `php artisan test --compact <path>`.
