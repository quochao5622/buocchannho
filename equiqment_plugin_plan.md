# Kế Hoạch Tạo Plugin Quản Lý Học Cụ

## 📋 Tổng Quan
Plugin sẽ quản lý học cụ (teaching equipment) với 2 module chính:
1. **Quản lý học cụ** - Danh sách, thêm/sửa/xóa học cụ
2. **Kiểm kê học cụ** - Phiếu kiểm kê và lịch sử kiểm kê

## 🗂️ Cấu Trúc Package
```
packages/equipment/
├── composer.json
├── phpunit.xml.dist
├── pint.json
├── phpstan.neon.dist
├── src/
│   ├── Models/
│   │   ├── EquipmentCategory.php  # Model danh mục
│   │   ├── Equipment.php          # Model học cụ
│   │   ├── EquipmentInventory.php # Model phiếu kiểm kê
│   │   └── EquipmentInventoryDetail.php
│   ├── Filament/
│   │   └── Resources/
│   │       ├── EquipmentCategoryResource.php
│   │       ├── EquipmentResource.php
│   │       └── EquipmentInventoryResource.php
│   ├── Commands/
│   ├── EquipmentPlugin.php
│   ├── EquipmentServiceProvider.php
│   └── Facades/
├── database/
│   ├── migrations/
│   │   ├── 2026_04_07_000000_create_equipment_categories_table.php
│   │   ├── 2026_04_07_000001_create_equipments_table.php
│   │   ├── 2026_04_07_000002_create_equipment_inventories_table.php
│   │   └── 2026_04_07_000003_create_equipment_inventory_details_table.php
│   └── factories/
│       ├── EquipmentCategoryFactory.php
│       ├── EquipmentFactory.php
│       └── EquipmentInventoryFactory.php
├── tests/
└── README.md
```

## 📊 Database Schema

### EquipmentCategory Table (Danh mục học cụ)
- `id` (primary key)
- `name` (string, unique)
- `description` (text, nullable)
- `code` (string, nullable) - mã danh mục
- `timestamps`

### Equipment Table
- `id` (primary key)
- `equipment_code` (string, unique)
- `name` (string)
- `category_id` (foreign key → equipment_categories)
- `image` (string, nullable) - đường dẫn hình ảnh
- `quantity` (integer) - số lượng hiện tại
- `status` (enum) - Tốt, Hỏng, Mất tích
- `location` (string, nullable) - vị trí học cụ
- `unit` (string) - đơn vị tính (cái, bộ, chiếc...)
- `note` (text, nullable)
- `timestamps`

### EquipmentInventory Table
- `id` (primary key)
- `inventory_code` (string, unique) - mã phiếu kiểm kê
- `inspector_id` (foreign key → users)
- `inventory_date` (date) - ngày kiểm kê
- `notes` (text, nullable)
- `status` (enum) - Draft, Completed, Approved
- `timestamps`

### EquipmentInventoryDetail Table
- `id` (primary key)
- `equipment_inventory_id` (foreign key)
- `equipment_id` (foreign key)
- `quantity_expected` (integer) - số lượng tính từ ghi nhận cuối cùng
- `quantity_actual` (integer) - số lượng thực tế
- `status` (enum) - Tốt, Hỏng, Mất tích
- `notes` (text, nullable)
- `timestamps`

## 🎯 Chức Năng Chi Tiết

### Module 0: Quản Lý Danh Mục (EquipmentCategoryResource) [Tùy chọn]
**Trang Danh Sách:**
- Hiệu chỉnh: mã, tên, mô tả
- Hành động: Thêm, Sửa, Xóa

### Module 1: Quản Lý Học Cụ (EquipmentResource)
**Trang Danh Sách:**
- Hiệu chỉnh: mã, tên, hình ảnh, số lượng, trạng thái
- Bộ lọc: theo danh mục, trạng thái
- Tìm kiếm: theo mã, tên
- Hành động: Thêm, Sửa, Xóa, Xem chi tiết

**Form Tạo/Sửa:**
- Mã học cụ (tự động generate hoặc nhập thủ công)
- Tên học cụ
- Danh mục (dropdown)
- Hình ảnh (file upload)
- Số lượng
- Trạng thái (Select: Tốt, Hỏng, Mất tích)
- Vị trí lưu trữ
- Đơn vị tính
- Ghi chú

### Module 2: Kiểm Kê Học Cụ (EquipmentInventoryResource)
**Trang Danh Sách Phiếu:**
- Hiệu chỉnh: mã phiếu, ngày kiểm kê, người kiểm kê, trạng thái
- Bộ lọc: theo trạng thái, ngày kiểm kê
- Hành động: Tạo mới, Xem chi tiết, Duyệt phiếu

**Form Tạo Phiếu:**
- Mã phiếu (tự động generate: INV-YYYYMMDD-XXX)
- Người kiểm kê (lấy từ `auth()->id()`) - read-only
- Ngày kiểm kê - mặc định hôm nay
- Bảng chi tiết kiểm kê (inline table)
  - Tất cả học cụ sẽ hiển thị
  - Số lượng ban gần đây nhất (read-only)
  - Số lượng thực tế (input)
  - Trạng thái (Select: Tốt, Hỏng, Mất tích)
  - Ghi chú (textarea)

**Hành Động:**
- Lưu nháp (Draft)
- Hoàn thành kiểm kê (Completed)
- Duyệt phiếu (Approved) - cập nhật số lượng trong Equipment

## 🔄 Workflow Kiểm Kê
1. Tạo phiếu kiểm kê mới → trạng thái Draft
2. Nhập số lượng thực tế cho từng học cụ
3. Hoàn thành phiếu → trạng thái Completed
4. Admin duyệt → trạng thái Approved + cập nhật số lượng trong Equipment table

## 📝 Reports/Views Bổ Sung (tùy chọn)
- Danh sách học cụ hỏng/mất tích
- Báo cáo lịch sử kiểm kê
- Sự khác biệt số lượng giữa ghi nhận và kiểm kê

## 📦 Dependencies
- `filament/filament: ^5.0`
- `filament/forms: ^5.0`
- `filament/tables: ^5.0`
- `spatie/laravel-package-tools: ^1.15.0`

## 🚀 Bước Thực Hiện
1. Tạo cấu trúc package (tương tự employee package)
2. Tạo 4 models: EquipmentCategory, Equipment, EquipmentInventory, EquipmentInventoryDetail
3. Tạo 4 migrations:
   - `create_equipment_categories_table.php`
   - `create_equipments_table.php`
   - `create_equipment_inventories_table.php`
   - `create_equipment_inventory_details_table.php`
4. Tạo 3 Filament Resources: EquipmentCategoryResource, EquipmentResource, EquipmentInventoryResource
5. Tạo EquipmentPlugin
6. Tạo EquipmentServiceProvider
7. Đăng ký plugin và dependencies
8. Tạo factories cho testing
9. Thêm plugin vào AdminPanelProvider.php
