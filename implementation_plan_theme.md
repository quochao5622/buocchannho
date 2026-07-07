# Kế Hoạch Chuyển Đổi Màu Sắc Theme (Light Mode) Theo Logo "Bước Chân Nhỏ"

Tài liệu này đề xuất phương án chuyển đổi bảng màu và nâng cấp giao diện **Light Mode** của hệ thống từ giao diện mặc định (sử dụng tông màu Amber đơn điệu) sang giao diện được thiết kế riêng dựa trên bộ nhận diện thương hiệu của logo **Bước Chân Nhỏ** (tông màu Hồng sen chủ đạo kết hợp Xanh lá và Slate).

---

## 1. Thiết Kế Bảng Màu (Color Palette Design)

Dựa trên màu sắc thực tế từ logo của trung tâm:
- **Màu Hồng sen (Pink/Magenta)**: Tương ứng với dấu chân bên phải và chữ "Chân". Đây là màu chủ đạo truyền tải sự ấm áp, yêu thương và năng lượng tích cực cho trẻ em.
  * *Mã màu đề xuất:* `#e5007d` (hoặc `#ec4899` để tăng độ tươi sáng trên màn hình).
- **Xanh lá (Green)**: Tương ứng với dấu chân bên trái và chữ "Bước". Đây là màu bổ trợ đại diện cho sự phát triển, bước tiến tự nhiên và hy vọng.
  * *Mã màu đề xuất:* `#13a850` (hoặc màu Emerald).
- **Màu Xám nền (Gray/Neutral)**: Sử dụng Slate thay vì Gray mặc định để mang lại cảm giác hiện đại, sạch sẽ và cao cấp.
  * *Mã màu đề xuất:* `Color::Slate`.

### Bảng Phân Bổ Màu Filament Đề Xuất:

| Trạng thái | Màu sắc mới | Giá trị (Filament/Tailwind) | Ghi chú |
| :--- | :--- | :--- | :--- |
| **Primary** | Hồng sen | `Color::hex('#e5007d')` | Sử dụng cho các nút chính, trạng thái active, link, icon chủ đạo |
| **Success** | Xanh lá | `Color::hex('#13a850')` | Sử dụng cho các thông báo thành công, badge trạng thái hợp lệ, điểm danh |
| **Warning** | Vàng cam | `Color::Amber` | Dùng cho cảnh báo, trạng thái chờ duyệt |
| **Danger** | Đỏ hồng | `Color::Rose` | Dùng cho lỗi, xóa, từ chối |
| **Info** | Xanh dương | `Color::Sky` | Dùng cho các thông tin hướng dẫn, liên kết phụ |
| **Gray** | Xám Slate | `Color::Slate` | Màu nền phụ, chữ tiêu chuẩn, border |

---

## 2. Các Đề Xuất Cải Tiến UI/UX (Premium Enhancements)

Để giao diện Light Mode không còn đơn điệu, kế hoạch đề xuất bổ sung các tùy chỉnh CSS trong [admin.css](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/public/css/admin.css) nhằm mang lại trải nghiệm tinh tế và cao cấp hơn:

### 2.1. Thanh Sidebar Ấn Tượng & Ý Nghĩa
* **Viền Sidebar**: Thay vì viền xám mặc định, sử dụng một đường viền mỏng màu hồng nhạt (`border-r border-pink-100`) để phân tách nhẹ nhàng.
* **Trạng thái Active (Đang chọn)**:
  * Nền: Chuyển màu gradient mềm mại từ hồng nhạt sang trắng (`linear-gradient(90deg, #fdf2f8 0%, #fff1f2 100%)`).
  * Chữ và Icon: Đổi sang màu Hồng sen (`#e5007d`).
  * **Điểm nhấn thương hiệu**: Thêm một thanh đứng nhỏ màu xanh lá (`#13a850`) dày 3px ở cạnh trái của menu item đang active. Điều này tượng trưng cho sự kết hợp giữa "Bước" (Xanh lá) và "Chân" (Hồng) từ logo, mang lại tính nhận diện thương hiệu độc đáo.
* **Trạng thái Hover**: Bo tròn các góc menu mềm mại hơn, chuyển động phóng to/thu nhỏ nhẹ nhàng khi hover chuột.

### 2.2. Hiệu Ứng Nổi Cho Thẻ Card & Widget
* Mặc định các card của Filament nằm phẳng trên nền. Đề xuất bổ sung hiệu ứng hover chuyển động nhẹ:
  * Khi hover: Card dịch chuyển lên trên 2px (`transform: translateY(-2px)`) kèm theo đổ bóng mờ tông hồng ấm (`box-shadow: 0 10px 20px -5px rgba(229,0,125,0.04), 0 8px 16px -8px rgba(0,0,0,0.04)`). Điều này giúp bảng điều khiển trông sinh động, trực quan hơn.

### 2.3. Thiết Kế Lại Các Nút Bấm Chính (Primary Buttons)
* Thay thế nút phẳng đơn sắc bằng nút có gradient nhẹ từ hồng tươi sang hồng đậm (`bg-gradient-to-r from-pink-500 to-pink-600`), kết hợp hiệu ứng transition mượt mà khi di chuột qua.

### 2.4. Trạng Thái Focus Đồng Bộ
* Đồng bộ hóa tất cả các viền khi focus vào ô nhập liệu (input), checkbox, radio hoặc các thành phần tương tác khác sang tông màu hồng sen để tránh việc xuất hiện màu xanh dương mặc định của trình duyệt.

---

## 3. Các File Sẽ Thay Đổi (Proposed Changes)

#### [MODIFY] [AdminPanelProvider.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/app/Providers/Filament/AdminPanelProvider.php)
* Cập nhật hàm `colors()` để định nghĩa các màu `primary`, `success`, `gray`, `info`, `warning`, `danger` sử dụng mã màu Hex thực tế từ Logo và các dải màu cao cấp của Filament.

#### [MODIFY] [admin.css](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/public/css/admin.css)
* Thêm các CSS rule tùy chỉnh cho lớp `.fi-main-sidebar`, `.fi-sidebar-item-active`, `.fi-sidebar-item:hover`, các thẻ `.fi-section`, `.fi-ta-container` (table), và nút bấm để tạo hiệu ứng chuyển động, gradient và đồng bộ hóa trải nghiệm Light Mode.

---

## 4. Kế Hoạch Xác Minh (Verification Plan)

### Kiểm Tra Thủ Công (Manual Verification)
1. Truy cập trang quản trị admin (`/admin/login` hoặc `/admin`).
2. Kiểm tra màu sắc Light Mode trên:
   - Các nút chính (Đăng nhập, Lưu, Thêm mới).
   - Sidebar: Độ tương phản của chữ, biểu tượng active (vạch xanh lá, nền hồng sen).
   - Bảng biểu (Tables): Border màu sắc dịu nhẹ hơn, các biểu tượng hành động đồng bộ.
   - Thẻ (Cards) trên dashboard: Hiệu ứng hover chuyển động nhẹ.
3. Kiểm tra xem Dark Mode có bị ảnh hưởng xấu bởi CSS tùy chỉnh hay không (đảm bảo các lớp CSS tùy chỉnh chỉ tác dụng ở Light Mode hoặc được xử lý an toàn với lớp `.dark`).
4. Chạy lệnh `vendor\bin\pint` để kiểm tra định dạng code PHP.
