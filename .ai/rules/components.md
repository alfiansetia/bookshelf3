---
paths:
    - 'resources/js/components/**'
---

# Components

## Reuse bookshelf components before writing new ones

Shared UI for the bookshelf domain lives in resources/js/components/bookshelf/ (BookCover, StarRating, StatusBadge, EmptyState, SectionHeading, StatCard, SocialIcon, SocialLinks, ShareButton, ShelfFilterBar, ShelfPagination, ProfileHero, ShelfThemePreview/Picker, CoverUploadField, BookForm, BookCard, shelves/_). Check that folder first and extend an existing component with a prop instead of duplicating markup. Files under components/ui/_ are vendored primitives: do not edit them.
