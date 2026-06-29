---
name: filament-resource-standard
description: Enforces the Filament Resource directory structure specific to this project, ensuring form and table schemas are separated into their own classes.
---

# Filament Resource Standard

This project uses a custom directory structure for Filament Resources. You MUST NEVER define `form()` or `table()` directly within the `Resource.php` file.

## Required Structure

When generating or refactoring a Filament Resource, it must follow this layout:

```
ComponentNameResource/
|- ComponentNameResource.php (Core resource definition)
|- Pages/ (Contains Create, Edit, List pages)
|- Schemas/
|  |- ComponentNameForm.php (Contains the form schema)
|- Tables/
   |- ComponentNameTable.php (Contains the table schema)
```

## Implementation Guide

1. **The Resource Class (`ComponentNameResource.php`)**:
   Instead of defining the schemas inline, call the external configuration classes:
   ```php
   use Vendor\Package\Filament\Resources\ComponentNameResource\Schemas\ComponentNameForm;
   use Vendor\Package\Filament\Resources\ComponentNameResource\Tables\ComponentNameTable;

   public static function form(Schema $schema): Schema
   {
       return ComponentNameForm::configure($schema);
   }

   public static function table(Table $table): Table
   {
       return ComponentNameTable::configure($table);
   }
   ```

2. **The Form Schema (`Schemas/ComponentNameForm.php`)**:
   ```php
   <?php
   namespace Vendor\Package\Filament\Resources\ComponentNameResource\Schemas;

   use Filament\Schemas\Schema;
   // ... other imports

   class ComponentNameForm
   {
       public static function configure(Schema $schema): Schema
       {
           return $schema->schema([
               // ... fields
           ]);
       }
   }
   ```

3. **The Table Schema (`Tables/ComponentNameTable.php`)**:
   ```php
   <?php
   namespace Vendor\Package\Filament\Resources\ComponentNameResource\Tables;

   use Filament\Tables\Table;
   // ... other imports

   class ComponentNameTable
   {
       public static function configure(Table $table): Table
       {
           return $table->columns([
               // ... columns
           ]);
       }
   }
   ```
