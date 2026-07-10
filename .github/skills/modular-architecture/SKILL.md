---
name: modular-architecture
description: Teaches the agent to navigate and work within the modular package-driven architecture of this Laravel project instead of the default app/ directory.
---

# Modular Architecture

This Laravel project uses a modular architecture. Instead of placing Models, Controllers, and Filament Resources in the default `app/` directory, they are separated by domains into the `packages/` directory.

## Package Structure

The `packages/` directory contains several modules:
- `acl`: Access Control List, Roles, Permissions, and Activity Logs.
- `core`: Core functionalities, Base User model, system audits.
- `employee`: Employee (Giáo viên) management.
- `equipment`: Equipment (Học cụ), Inventory, and Categories.
- `planning_evaluation`: Lesson Plannings (Kế hoạch) and Evaluations (Đánh giá).
- `student`: Student (Học sinh) management.

## Navigation Guidelines

When you are asked to work on a feature, you MUST locate the relevant files inside `packages/<domain_name>/src/` rather than `app/`.

For example, if asked to modify the `Student` model or its Filament Resource:
- **Incorrect:** `app/Models/Student.php`
- **Correct:** `packages/student/src/Models/Student.php`

- **Incorrect:** `app/Filament/Resources/StudentResource.php`
- **Correct:** `packages/student/src/Filament/Resources/StudentResource.php`

## Namespaces

The base namespace for the project's packages is `Quochao56\<PackageName>`. For example, `packages/student/src/Models/Student.php` has the namespace `Quochao56\Student\Models`.
