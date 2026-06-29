# Customization Rules

## Language and Translation Files
- **Do not create, maintain, or update English translation files (`lang/en`).**
- **Only write, use, and update Vietnamese (`lang/vi`) translation files.**
- If any English translations are created temporarily or by mistake, they must be removed. All UI and validation text must be dynamically translated using the Vietnamese localization files.

## PHP Code Cleanliness & Imports
- **Always place `use` import statements at the top of PHP files, directly under the `namespace` declaration.**
- **Do not use inline fully qualified class names (FQCNs)** inside class methods or properties. Import them at the top of the file to keep the implementation code clean and readable. Use class aliases (`as`) when importing classes with duplicate names from different namespaces.
