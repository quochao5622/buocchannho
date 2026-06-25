# Hướng dẫn Phát triển & Quy chuẩn Code cho Agent (Agent Coding Guidelines)

Tài liệu này định nghĩa cấu trúc thư mục, quy chuẩn code, và các lưu ý quan trọng để đảm bảo tính đồng nhất, chính xác và hiệu quả khi tự động sinh code hoặc bảo trì dự án **buocchannho**.

---

## 1. Kiến trúc Gói (Package Architecture)
Dự án được cấu trúc theo dạng mô-đun hóa (modularized). Các tính năng cốt lõi được chia thành các package tự chứa (self-contained packages) nằm trong thư mục `packages/`:
* `packages/core`: Các thành phần dùng chung cốt lõi.
* `packages/employee`: Quản lý Giáo viên/Nhân viên.
* `packages/student`: Quản lý Học sinh.
* `packages/planning_evaluation`: Phân hệ Kế hoạch & Đánh giá (giao việc, xem lịch sử, theo dõi tiến độ).
* `packages/equipment`: Quản lý Thiết bị/Cơ sở vật chất.

### Nguyên tắc thiết kế:
* **Tự chứa (Self-contained):** Các models, migrations, views, controllers, resources, và pages của phân hệ nào phải nằm trọn vẹn trong thư mục của package đó.
* **Đăng ký Service Provider:** Đăng ký các routes, views, migrations, và translations trong Service Provider của từng package (ví dụ: `PlanningEvaluationServiceProvider`).
* **Đăng ký Plugin:** Đăng ký tài nguyên Filament (Resources, Pages, Widgets) qua lớp Plugin chuyên biệt (ví dụ: `PlanningEvaluationPlugin`) và nạp nó vào `AdminPanelProvider.php` của ứng dụng chính.

---

## 2. Quy chuẩn Laravel & Database
* **Migrations:** Tất cả các thay đổi về cấu trúc bảng database phải được thực hiện qua migrations trong thư mục `database/migrations` của package tương ứng.
* **Eloquent Relations:** Định nghĩa rõ ràng kiểu trả về cho các mối quan hệ (ví dụ: `hasMany`, `belongsTo`, `hasOneThrough`). Sử dụng `whereNull('unassigned_at')` hoặc các điều kiện tương tự để lấy trạng thái hoạt động hiện tại (Active).
* **Bảo vệ dữ liệu:** Luôn luôn gán cấu hình `$fillable` hoặc `$casts` thích hợp (ví dụ: casting ngày tháng sang `date` hoặc `datetime`, casting dữ liệu phức tạp sang `array`).

---

## 3. Quy chuẩn Filament v5 (Filament v5 Conventions)
Dự án sử dụng **Filament v5**. Cần tuân thủ nghiêm ngặt các thay đổi cấu trúc của v5:

### 3.1. Đồng nhất Namespace cho Action
* Không sử dụng namespace cũ `Filament\Tables\Actions\*` hoặc `Filament\Pages\Actions\*` vốn đã lỗi thời và không tồn tại.
* **BẮT BUỘC** sử dụng namespace thống nhất: **`Filament\Actions\*`** cho tất cả các loại Action (bao gồm cả Action trong Table, Page, Form, BulkAction).
  * Ví dụ đúng:
    ```php
    use Filament\Actions\Action;
    use Filament\Actions\CreateAction;
    use Filament\Actions\EditAction;
    use Filament\Actions\DeleteAction;
    use Filament\Actions\ViewAction;
    use Filament\Actions\BulkActionGroup;
    use Filament\Actions\DeleteBulkAction;
    ```

### 3.2. Bản địa hóa động (Dynamic Localization)
* **Quy tắc:** Tuyệt đối không khai báo văn bản hiển thị tĩnh (hardcoded string) cho các thuộc tính static trong class của Filament (như `$title`, `$navigationLabel`, `$navigationGroup`, `$modelLabel`, `$pluralModelLabel`). PHP không hỗ trợ gọi hàm dịch `trans()` tại thời điểm khởi tạo thuộc tính static.
* **Giải pháp:** Override các phương thức getter tương ứng để trả về chuỗi dịch động:
  * Ví dụ đúng cho Page:
    ```php
    public static function getNavigationLabel(): string
    {
        return trans('packages.planning_evaluation::planning.progress.nav_label');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return trans('packages.planning_evaluation::planning.progress.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.planning_evaluation::planning.navigation_group');
    }
    ```
  * Ví dụ đúng cho Resource/RelationManager:
    ```php
    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return trans('packages.planning_evaluation::planning.assignment.title');
    }
    ```

### 3.3. Tải Widget thủ công trên Custom Page
* Nếu cần tạo Widget dạng biểu đồ hoặc thống kê đi kèm riêng cho một Custom Page (ví dụ: biểu đồ tiến độ trong `StudentProgressReport`), hãy render Widget trực tiếp bằng chỉ thị `@livewire` trong Blade view của Page đó thay vì đăng ký tự động (discover) để tránh hiển thị tràn lan trên Dashboard chính.
  * Ví dụ đúng:
    ```html
    @livewire(\Quochao56\PlanningEvaluation\Filament\Widgets\StudentProgressChart::class, ['studentId' => $this->studentId])
    ```

---

## 4. Quy chuẩn Bản địa hóa (Localization Guidelines)
* Tất cả các văn bản hiển thị trên UI bên ngoài (nhãn cột, placeholder bộ lọc, tiêu đề biểu đồ, thông báo thành công/thất bại, nhãn nút bấm) phải được dịch và đặt trong file ngôn ngữ gói.
* **Đường dẫn tệp tiếng Việt:** `packages/planning_evaluation/lang/vi/planning.php`.
* **Cách gọi:** Sử dụng `trans('packages.planning_evaluation::planning.prefix.key')` hoặc trợ giúp `__('...')`.
* Cấu trúc tệp dịch nên được phân cấp rõ ràng theo tính năng (ví dụ: `fields`, `actions`, `history`, `assignment`, `clone`, `progress`, `tracker`).

---

## 5. Quy trình Kiểm thử (Testing Guidelines)
* Dự án sử dụng **Pest PHP** làm framework kiểm thử chính.
* Vì tệp cấu hình `phpunit.xml` ở gốc có thể không tự quét các thư mục con trong `packages`, khi chạy kiểm thử cho một phân hệ cụ thể, luôn chạy thông qua Pest bằng cách chỉ định trực tiếp đường dẫn thư mục kiểm thử của package đó.
  * Lệnh chạy đúng:
    ```bash
    .\vendor\bin\pest packages/planning_evaluation/tests
    ```
* **Yêu cầu kiểm thử:** Mỗi khi thay đổi logic nghiệp vụ lớn (ví dụ: nhân bản bản ghi, phân công giáo viên gán lịch sử), phải bổ sung các test cases kiểm chứng tương ứng trong thư mục `tests` của package.
