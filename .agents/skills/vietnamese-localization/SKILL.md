---
name: vietnamese-localization
description: Standard guidelines for managing, writing, and dynamically loading Vietnamese localization files (lang/vi) without hardcoding text labels in views or PHP files.
---

# Vietnamese Localization Standard

This skill guides on how to properly implement and use Vietnamese translation files in this package-driven architecture.

## Guidelines:
1. **Do not create, maintain, or update English translation files (`lang/en`).**
2. **Only write, use, and update Vietnamese (`lang/vi`) translation files.**
3. If any English translations are created temporarily or by mistake, they must be removed. All UI and validation text must be dynamically translated using the Vietnamese localization files.
4. **Never hardcode string values or UI text labels directly in PHP or Blade view files.** All labels, placeholders, titles, status texts, error/success notification messages, and other UI texts must be defined in the Vietnamese translation files (`packages/*/lang/vi/*.php`) and retrieved dynamically via `trans(...)` or `__('...')`.
