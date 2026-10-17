---
paths:
    - 'app/Http/Resources/**'
---

# Resources

## Inertia payloads always go through resources

Send book/profile data with BookResource::forShelf($book) and ShelfProfileResource; never pass raw models to Inertia::render(). Every new field must be mirrored in resources/js/types/bookshelf.ts, and the `can` block (update/delete) is what the frontend uses for authorisation-driven UI.
