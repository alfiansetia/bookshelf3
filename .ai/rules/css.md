---
paths:
    - 'resources/css/**'
---

# Css

## Brand design tokens live in app.css

All colours, radii, fonts, and shelf-theme variables are declared as CSS custom properties in resources/css/app.css and mapped through `@theme inline`. Never hardcode brand hex/hsl values in components; use the semantic utilities (bg-primary, text-muted-foreground, bg-shelf-surface, text-shelf-ink, ...). Dark mode is opt-out via the `.dark` class, so every new token needs a `.dark` override.
