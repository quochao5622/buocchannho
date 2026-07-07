---
name: acl-permissions
description: Guides the agent on how to properly add, update, and seed permissions in this Spatie Permission configured project.
---

# ACL Permissions Management

This project uses Spatie Permission for Access Control, but it manages permission definitions dynamically through a configuration file rather than manual Database Seeder arrays.

## Adding New Permissions

When you need to add a new permission (e.g. for a new Filament Resource), you MUST do so by modifying the `packages/acl/config/permissions.php` file.

1. **Locate the config:** Open `packages/acl/config/permissions.php`.
2. **Add the group:** Add a new array group with its `label`, `icon`, and `permissions` list.
   ```php
   'group_name' => [
       'label' => 'Tên nhóm (Tiếng Việt)',
       'icon' => 'heroicon-o-icon-name',
       'permissions' => [
           'index' => 'Xem danh sách',
           'create' => 'Thêm mới',
           'edit' => 'Chỉnh sửa',
           'show' => 'Xem chi tiết',
           'destroy' => 'Xóa',
       ],
   ],
   ```

## Syncing Permissions to Database

After modifying `permissions.php`, you MUST run the `RolesAndPermissionsSeeder` to sync the newly defined permissions into the database:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

This seeder will automatically clear the Spatie cache, create any missing permissions defined in the config, and delete any permissions from the DB that have been removed from the config.
