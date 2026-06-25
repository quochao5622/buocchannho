# Lộ trình triển khai Phân hệ Kế hoạch & Đánh giá (buocchannho)

Tài liệu này chi tiết kế hoạch kỹ thuật để thực hiện 5 tính năng mới trong package `planning_evaluation` và liên kết với các package khác (`student`, `employee`) của dự án **buocchannho**:
1. **Giao diện xem lịch sử thay đổi (History UI)** cho Kế hoạch (Planning) và Đánh giá (Evaluation).
2. **Nhân bản kế hoạch (Clone Plan Action)** để tái sử dụng khung kế hoạch nhanh chóng.
3. **Báo cáo Tiến độ Học sinh (Progress Report Page & Chart Widget)** bằng biểu đồ trực quan hóa dữ liệu đánh giá.
4. **Trang Theo dõi gửi Kế hoạch & Đánh giá (Submission Tracker)** kèm chức năng **Xuất file Excel** báo cáo.
5. **Tính năng Ghép nhóm Giáo viên - Học sinh (Teacher-Student Assignment)**: Quản lý phân công giáo viên chủ quản cho học sinh, lưu lịch sử phân công, và lọc kế hoạch/đánh giá theo giáo viên phụ trách.

---

## Giao diện & Thiết kế (User Review Required)

> [!NOTE]
> * **Vị trí của Giao diện Lịch sử:** Lịch sử thay đổi sẽ được nhúng trực tiếp làm một **Relation Manager (tab con)** ở phía cuối trang Chỉnh sửa (Edit) của Kế hoạch và Đánh giá. Mỗi khi có thay đổi được lưu, bản ghi mới sẽ xuất hiện ở bảng này. Click "Xem chi tiết" sẽ hiển thị một Modal slide-over với định dạng bảng đẹp mắt thể hiện snapshot dữ liệu tại thời điểm đó.
> * **Nhân bản Kế hoạch:** Hỗ trợ nhân bản trực tiếp qua Table Action ở danh sách Kế hoạch (`ListPlannings`) và Header Action ở trang Chỉnh sửa Kế hoạch (`EditPlanning`). Khi nhân bản, hệ thống hiển thị một Modal yêu cầu chọn **Học sinh mới**, **Ngày bắt đầu** và **Ngày kết thúc**, các nội dung chi tiết mục tiêu sẽ được sao chép nguyên vẹn nhưng đặt ở trạng thái Nháp (`Draft`).
> * **Trang Báo cáo Tiến độ:** Sẽ được đăng ký là một Menu riêng biệt mang tên **Báo cáo tiến độ** dưới nhóm menu "Học tập" trong Filament. Trang này chứa một ô chọn Học sinh (Select Student), và khi chọn bé nào, hệ thống hiển thị biểu đồ Chart.js (Line Chart) thống kê các mục tiêu bé đạt được (`+`, `+/-`, `-`) qua các mốc thời gian đánh giá.
> * **Trang Theo dõi gửi Kế hoạch & Đánh giá (Tracker):**
>   * Đăng ký một menu riêng tên là **Theo dõi nộp KH & ĐG** dưới nhóm "Học tập".
>   * Giao diện gồm các bộ lọc ở trên cùng (`AboveContent`):
>     1. **Loại theo dõi** (Kế hoạch / Đánh giá)
>     2. **Thời gian** (Tháng/Năm)
>     3. **Giáo viên chủ quản** (Select từ danh sách Giáo viên) - Mặc định hiển thị toàn bộ giáo viên và học sinh. Nếu chọn một giáo viên cụ thể, bảng sẽ chỉ lọc danh sách học sinh thuộc quyền quản lý của giáo viên đó.
>     4. **Chỉ xem học sinh của tôi** (Toggle) - Tự động lọc học sinh theo giáo viên đang đăng nhập (khớp email User và Employee).
>   * Bảng hiển thị danh sách học sinh. Cột: Mã học sinh, Tên học sinh, Biệt danh (Nickname), Trạng thái (Đã có / Chưa có), Giáo viên phụ trách (người lập KH/ĐG thực tế), Giáo viên chủ quản (người được giao quản lý bé hiện tại).
>   * Hành động (Row Action): Nút "Xem chi tiết" chuyển hướng sang trang chỉnh sửa Kế hoạch/Đánh giá.
>   * **Nút Export Excel:** Thêm nút xuất file Excel toàn bộ bảng dữ liệu hiện tại theo bộ lọc đang chọn (sử dụng Laravel Excel / Spatie Simple Excel).
> * **Ghép nhóm Giáo viên - Học sinh:**
>   * Nhúng một Relation Manager mang tên **Lịch sử giáo viên phụ trách** vào trang Edit của `StudentResource`.
>   * Tại đây hiển thị danh sách giáo viên phụ trách qua các thời kỳ, cột: Giáo viên, Ngày bắt đầu gán (`assigned_at`), Ngày kết thúc (`unassigned_at`).
>   * Nút "Gán giáo viên mới" sẽ mở form chọn giáo viên. Khi lưu, hệ thống tự động cập nhật ngày kết thúc (`unassigned_at = now()`) cho giáo viên hiện tại, rồi tạo dòng gán mới cho giáo viên vừa chọn.

---

## Open Questions

> [!IMPORTANT]
> * **Logic liên kết giữa tài khoản đăng nhập (User) và Giáo viên (Employee):** Hệ thống hiện có 2 bảng độc lập `users` và `employees` không liên kết khóa ngoại. Chúng tôi đề xuất liên kết dựa trên trường `email` của 2 bảng để xác định giáo viên nào đang đăng nhập nhằm phục vụ tính năng lọc "Học sinh của tôi". Bạn có đồng ý với logic này? (Đã cập nhật để chuẩn bị triển khai)

---

## Proposed Changes

### Component: planning_evaluation package

#### [NEW] [create_student_assignments_table.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/database/migrations/2026_06_23_000000_create_student_assignments_table.php)
* Tạo bảng `student_assignments` để lưu lịch sử phân công giáo viên quản lý học sinh:
  * `id`
  * `student_id` (foreign key -> students)
  * `employee_id` (foreign key -> employees)
  * `assigned_at` (datetime)
  * `unassigned_at` (datetime, nullable)
  * `timestamps`

#### [NEW] [StudentAssignment.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/Models/StudentAssignment.php)
* Định nghĩa Model quản lý phân công với các quan hệ `student()` và `employee()`.

#### [MODIFY] [Student.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/student/src/Models/Student.php)
* Thêm quan hệ `assignments()` (HasMany `StudentAssignment`), `currentAssignment()` (HasOne `StudentAssignment` lọc `unassigned_at is null`) và `currentTeacher()` (HasOneThrough `Employee` qua `StudentAssignment`).

#### [NEW] [StudentAssignmentRelationManager.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/Filament/Resources/StudentAssignmentRelationManager.php)
* Tạo Relation Manager quản lý phân công gán giáo viên cho Học sinh.
* Hiển thị bảng lịch sử gán. Thêm nút "Gán giáo viên mới" thực hiện đóng gán cũ và tạo gán mới.

#### [MODIFY] [PlanningHistory.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/Models/PlanningHistory.php)
* Thêm quan hệ `user()` liên kết với `App\Models\User` qua `saved_by`.

#### [MODIFY] [EvaluationHistory.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/Models/EvaluationHistory.php)
* Thêm quan hệ `user()` liên kết với `App\Models\User` tương tự.

#### [NEW] [PlanningHistoryRelationManager.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/Filament/Resources/Plannings/RelationManagers/PlanningHistoryRelationManager.php)
* Hiển thị lịch sử thay đổi của Kế hoạch kèm nút "Xem chi tiết" (dùng custom modal content).

#### [NEW] [EvaluationHistoryRelationManager.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/Filament/Resources/Evaluations/RelationManagers/EvaluationHistoryRelationManager.php)
* Giao diện xem lịch sử đánh giá.

#### [NEW] [planning-history-view.blade.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/resources/views/filament/resources/planning-history-view.blade.php)
* File Blade hiển thị chi tiết snapshot kế hoạch dạng bảng HTML.

#### [NEW] [evaluation-history-view.blade.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/resources/views/filament/resources/evaluation-history-view.blade.php)
* File Blade hiển thị chi tiết snapshot đánh giá dạng bảng HTML.

#### [MODIFY] [PlanningResource.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/Filament/Resources/Plannings/PlanningResource.php)
* Đăng ký `PlanningHistoryRelationManager::class`.

#### [MODIFY] [EvaluationResource.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/Filament/Resources/Evaluations/EvaluationResource.php)
* Đăng ký `EvaluationHistoryRelationManager::class`.

#### [MODIFY] [PlanningsTable.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/Filament/Resources/Plannings/Tables/PlanningsTable.php)
* Bổ sung Action `clone` vào dòng bảng và Header Action trang chỉnh sửa.
* Thêm Table Filter "Giáo viên chủ quản" để lọc các kế hoạch có học sinh đang được quản lý bởi giáo viên được chọn.

#### [MODIFY] [EditPlanning.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/Filament/Resources/Plannings/Pages/EditPlanning.php)
* Bổ sung Header Action `clone`.

#### [NEW] [StudentProgressReport.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/Filament/Pages/StudentProgressReport.php)
* Trang báo cáo tiến bộ học sinh kèm Select Student.

#### [NEW] [StudentProgressChart.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/Filament/Widgets/StudentProgressChart.php)
* Widget Line Chart vẽ phần trăm mục tiêu hoàn thành của học sinh.

#### [NEW] [PlanningEvaluationTracker.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/Filament/Pages/PlanningEvaluationTracker.php)
* Trang theo dõi nộp KH & ĐG. Hiển thị danh sách học sinh.
* Tích hợp bộ lọc trên đầu bảng: Loại (KH/ĐG), Tháng/Năm, Giáo viên chủ quản (nếu trống sẽ hiển thị tất cả), Chỉ xem học sinh của tôi (khớp email user đăng nhập).
* Thêm nút Export Excel: Sử dụng Laravel Excel / Spatie Simple Excel để kết xuất danh sách học sinh đang hiển thị trên bảng ra file Excel kèm trạng thái và thông tin giáo viên.

#### [MODIFY] [PlanningEvaluationPlugin.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/src/PlanningEvaluationPlugin.php)
* Đăng ký trang báo cáo `StudentProgressReport::class` và trang theo dõi `PlanningEvaluationTracker::class` trong Filament panel.

#### [MODIFY] [planning.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/lang/vi/planning.php)
* Bổ sung các chuỗi dịch cần thiết cho phân công giáo viên chủ quản, lịch sử gán, và các bộ lọc nộp kế hoạch.

#### [MODIFY] [evaluation.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/planning_evaluation/lang/vi/evaluation.php)
* Bổ sung dịch nghĩa tiếng Việt liên quan đến lịch sử đánh giá.

### Component: student package

#### [MODIFY] [StudentResource.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/packages/student/src/Filament/Resources/StudentResource.php)
* Đăng ký `StudentAssignmentRelationManager::class` trong hàm `getRelations()` để quản trị viên có thể gán giáo viên ngay tại trang chi tiết học sinh.
* Thêm Table Bulk Action `assign_teacher` để gán giáo viên chủ quản hàng loạt cho các học sinh được chọn.

#### [NEW] [PlanningEvaluationSeeder.php](file:///d:/haolq/laravel/opsgreat/gitlab/buocchannho/database/seeders/PlanningEvaluationSeeder.php)
* Tạo lớp Seeder để khởi tạo dữ liệu mẫu cho hệ thống Kế hoạch & Đánh giá:
  * Tạo tài khoản người dùng (`User`) và hồ sơ giáo viên (`Employee`) tương ứng để phục vụ kiểm thử tính năng lọc theo giáo viên đăng nhập.
  * Tạo danh sách Học sinh mẫu.
  * Phân công Giáo viên chủ quản cho học sinh (cả bản ghi hiện tại và lịch sử đã kết thúc).
  * Lập Kế hoạch mẫu với nội dung chi tiết mục tiêu (JSON) và các Đánh giá tương ứng qua các mốc thời gian để vẽ biểu đồ tiến độ.

---

## Verification Plan

### Automated Tests
* Tạo test case mới: `packages/planning_evaluation/tests/Feature/ClonePlanningTest.php`
  * Thực hiện test nhân bản một Planning, kiểm tra sao chép đúng mục tiêu và gán trạng thái Draft.
* Tạo test case mới: `packages/planning_evaluation/tests/Feature/StudentAssignmentTest.php`
  * Test tính năng gán giáo viên: Gán giáo viên A -> Gán tiếp giáo viên B -> Kiểm tra giáo viên A có `unassigned_at` và giáo viên B là `currentTeacher`.

### Manual Verification
1. **Kiểm tra Phân công Giáo viên Hàng loạt (Bulk Action):**
   * Truy cập danh sách Học sinh (`StudentResource` Index).
   * Chọn nhiều học sinh bằng Checkbox, bấm vào nút Hành động hàng loạt -> Chọn "Gán giáo viên chủ quản".
   * Chọn giáo viên và thời điểm gán. Lưu lại và xác nhận dữ liệu cập nhật chính xác cho toàn bộ học sinh được chọn.
2. **Kiểm tra Phân công Giáo viên (StudentAssignment UI):**
   * Truy cập trang chỉnh sửa Học sinh. Xem mục "Lịch sử giáo viên phụ trách".
   * Bấm "Gán giáo viên mới", chọn giáo viên. Xác nhận dòng gán mới xuất hiện và có trạng thái active.
   * Gán giáo viên khác, kiểm tra xem dòng gán cũ có tự động điền Ngày kết thúc.
3. **Kiểm tra Trang Theo dõi gửi KH & ĐG và Bộ lọc Giáo viên:**
   * Truy cập trang "Theo dõi nộp KH & ĐG".
   * Chọn bộ lọc "Giáo viên chủ quản". Xác nhận chỉ hiển thị các học sinh được gán cho giáo viên đó.
   * Nhấn nút Export Excel, mở file tải về kiểm tra cột trạng thái và danh sách học sinh khớp hoàn toàn với màn hình.
4. **Kiểm tra Lịch sử thay đổi & Nhân bản:**
   * Thực hiện các thao tác sửa kế hoạch để lưu history và bấm nhân bản để kiểm tra.
5. **Chạy Seeder:**
   * Chạy lệnh `php artisan db:seed --class=PlanningEvaluationSeeder` để kiểm tra quá trình tạo dữ liệu mẫu không bị lỗi.
