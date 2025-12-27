# KBoard Development Rules & Guidelines

## 1. Golden Standard: The 'default' Skin
- **The `default` skin is the absolute source of truth (Golden Source).**
- When styling or modifying other skins (e.g., `contact-form`), ALWAYS reference `skin/default/style.css` and its PHP files first.
- Do not create arbitrary styles if a pattern already exists in the `default` skin.
- **Copy & Paste with Care**: Ensure ALL dependencies (like CSS variables in `:root`, specific classes) are copied over. Do not miss variables like `--kboard-spacing-md`.

## 2. CSS & Styling
- **CSS Variables**: Check `skin/default/style.css`'s `:root` section. Ensure target skins define the same variables to maintain consistency across the plugin.
- **Spacing**: Use standard spacing variables (e.g., `var(--kboard-spacing-md)`) instead of hardcoded pixels (e.g., `16px`) when possible, to match the `default` design system.
- **Structure**: Maintain the same HTML structure and class hierarchy as the `default` skin unless functionality specifically demands a change.

## 3. Workflow
- Before making significant changes to a skin, verify how the `default` skin handles the component (List, Editor, Document, etc.).
- If a user reports missing styles or layout issues, compare strictly against the `default` skin implementation.
