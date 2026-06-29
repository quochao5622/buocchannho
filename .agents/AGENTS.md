# Customization Rules

## Language and Translation Files
- **Do not create, maintain, or update English translation files (`lang/en`).**
- **Only write, use, and update Vietnamese (`lang/vi`) translation files.**
- If any English translations are created temporarily or by mistake, they must be removed. All UI and validation text must be dynamically translated using the Vietnamese localization files.

## PHP Code Cleanliness & Imports
- **Always place `use` import statements at the top of PHP files, directly under the `namespace` declaration.**
- **Do not use inline fully qualified class names (FQCNs)** inside class methods or properties. Import them at the top of the file to keep the implementation code clean and readable. Use class aliases (`as`) when importing classes with duplicate names from different namespaces.
- Always ensure to run `vendor\bin\pint` after modifying code to guarantee clean style and structure.

## Filament Architecture
- Filament Resources **MUST** follow the directory structure pattern:
  - `Resource.php` (Core resource definition)
  - `Pages/` (List, Edit, Create pages)
  - `Schemas/` (Form schemas separated into Form classes e.g., `EntityForm.php`)
  - `Tables/` (Table schemas separated into Table classes e.g., `EntityTable.php`)
- Do not define `form()` and `table()` bodies inline inside the `Resource.php`. Instead, call `EntityForm::configure($schema)` and `EntityTable::configure($table)`.
