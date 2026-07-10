Viewed README.md:5-32

Dựa trên cấu trúc kiến trúc dạng Module (Package-driven) hiện tại của dự án (đã có các phân hệ cốt lõi như: *Học sinh, Nhân sự, Lập kế hoạch & Đánh giá mục tiêu IEP, Quản lý học cụ*), dự án này cực kỳ lý tưởng để mở rộng thành một hệ thống quản trị toàn diện (**Special Education ERP**) cho trung tâm dạy trẻ đặc biệt.

Dưới đây là các tính năng thiết thực nhất có thể mở rộng, chia theo nhóm nghiệp vụ đặc thù của ngành giáo dục đặc biệt:

---

### 1. Phân hệ Đánh giá đầu vào & Sàng lọc phát triển (Intake Assessment & Screening)
Trước khi lập kế hoạch giáo dục cá nhân (IEP), trẻ đặc biệt luôn cần được sàng lọc và đánh giá mức độ phát triển để xác định phân lớp/trị liệu phù hợp.
*   **Số hóa các bộ công cụ kiểm tra chuẩn**: Tích hợp các biểu mẫu trắc nghiệm đánh giá phát triển và hành vi phổ biến tại Việt Nam như:
    *   **PEP-3** (Hồ sơ tâm lý giáo dục sửa đổi).
    *   **CARS** (Thang đánh giá tự kỷ ở trẻ em).
    *   **ASQ-3** (Bảng câu hỏi độ tuổi và giai đoạn phát triển).
    *   **M-CHAT-R** (Sàng lọc tự kỷ ở trẻ mới biết đi).
*   **Vẽ biểu đồ biểu đồ phát triển tự động**: Tự động tính điểm và vẽ biểu đồ hình mạng nhện (radar chart) so sánh độ tuổi phát triển thực tế của trẻ so với tuổi sinh học trên các lĩnh vực: *vận động thô, vận động tinh, nhận thức, ngôn ngữ nhận biết, ngôn ngữ diễn đạt, cá nhân - xã hội*.

### 2. Phân hệ Nhật ký hoạt động & Nhật ký Trị liệu Hàng ngày (Daily Therapy & Session Logs)
Giáo viên và nhà trị liệu cần ghi chép nhanh các diễn biến hàng ngày để theo dõi sát sao hành vi và cảm xúc của trẻ.
*   **Nhật ký buổi học (Daily Logs)**: Ghi chép nhanh các chỉ số cơ bản của trẻ trong ngày: *trạng thái cảm xúc, khả năng tập trung, mức độ hợp tác, ăn uống, ngủ nghỉ, vệ sinh*.
*   **Ghi chép hành vi đặc biệt (Behavior Incident Reports - ABC Chart)**: Ghi chép các hành vi thách thức (gào khóc, tự làm đau, ăn vạ) theo mô hình:
    *   **A (Antecedent)**: Hoàn cảnh trước khi xảy ra hành vi.
    *   **B (Behavior)**: Mô tả cụ thể hành vi của trẻ.
    *   **C (Consequence)**: Phản ứng của giáo viên và kết quả.
    *   *Mục đích: Tìm ra nguyên nhân kích thích hành vi của trẻ để điều chỉnh phương pháp.*

### 3. Phân hệ Quản lý Lịch học & Lịch Trị liệu cá nhân (Therapy & Class Scheduler)
Trẻ đặc biệt thường có lịch phối hợp linh hoạt giữa các lớp học nhóm (can thiệp nhóm) và các giờ can thiệp cá nhân 1-1 (trị liệu ngôn ngữ, hoạt động trị liệu OT, vật lý trị liệu PT).

#### A. Kiến trúc Package (`packages/scheduler`)
Phân hệ được thiết kế dưới dạng package độc lập theo kiến trúc Modular của hệ thống:
*   **Namespace**: `Quochao56\Scheduler`
*   **Cấu trúc thư mục**:
    ```text
    packages/scheduler/
    ├── database/migrations/
    │   └── 2026_07_01_000000_create_scheduler_tables.php
    ├── config/
    │   └── scheduler.php
    └── src/
        ├── Models/
        │   ├── Schedule.php           (Lịch học lặp lại/một lần)
        │   ├── ScheduleException.php  (Lịch nghỉ, đổi giờ, dạy thay)
        │   └── Attendance.php         (Điểm danh & ghi nhận giờ can thiệp)
        ├── Rules/
        │   └── NoConflictScheduleRule.php (Validation tránh trùng lịch)
        ├── Filament/
        │   └── Resources/
        │       ├── ScheduleResource/
        │       │   ├── ScheduleResource.php
        │       │   ├── Pages/
        │       │   ├── Schemas/
        │       │   │   └── ScheduleForm.php
        │       │   └── Tables/
        │       │       └── ScheduleTable.php
        │       └── AttendanceResource/
        │           ├── AttendanceResource.php
        │           ├── Pages/
        │           ├── Schemas/
        │           │   └── AttendanceForm.php
        │           └── Tables/
        │               └── AttendanceTable.php
        ├── SchedulerServiceProvider.php
        └── SchedulerPlugin.php
    ```

#### B. Thiết kế Cơ sở Dữ liệu (Database Schema)

##### 1. Bảng `schedules` (Lưu cấu hình lịch biểu)
```php
Schema::create('schedules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
    $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
    $table->foreignId('equipment_id')->nullable()->constrained('equipments')->nullOnDelete(); // Phòng chức năng
    $table->string('title'); // Ví dụ: "Can thiệp Ngôn ngữ 1-1", "Trị liệu OT"
    $table->enum('type', ['individual', 'group'])->default('individual');
    $table->unsignedTinyInteger('day_of_week')->nullable(); // 1: Chủ nhật, 2-7: Thứ 2 - Thứ 7. Null nếu là lịch một lần.
    $table->time('start_time');
    $table->time('end_time');
    $table->date('start_date');
    $table->date('end_date')->nullable(); // Null nếu lịch kéo dài vô hạn
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

##### 2. Bảng `schedule_exceptions` (Xử lý các thay đổi đột xuất/ngoại lệ)
```php
Schema::create('schedule_exceptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
    $table->date('exception_date'); // Ngày cụ thể phát sinh ngoại lệ
    $table->enum('action', ['cancel', 'reschedule', 'substitute']); // Hủy, dời lịch, dạy thay
    $table->foreignId('new_employee_id')->nullable()->constrained('employees')->nullOnDelete(); // Giáo viên dạy thay
    $table->foreignId('new_equipment_id')->nullable()->constrained('equipments')->nullOnDelete(); // Phòng thay thế
    $table->time('new_start_time')->nullable();
    $table->time('new_end_time')->nullable();
    $table->text('reason')->nullable(); // Lý do hủy buổi, dạy thay (ốm, nghỉ phép, bận việc...)
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

##### 3. Bảng `attendances` (Kết quả điểm danh & Tính giờ trị liệu thực tế)
```php
Schema::create('attendances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
    $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
    $table->date('attendance_date');
    $table->enum('status', ['present', 'absent_excused', 'absent_unexcused', 'late'])->default('present');
    $table->dateTime('check_in_at')->nullable();
    $table->dateTime('check_out_at')->nullable();
    $table->decimal('total_hours', 4, 2)->nullable(); // Số giờ thực tế phục vụ tính học phí
    $table->text('notes')->nullable();
    $table->foreignId('verified_by_employee_id')->constrained('employees')->cascadeOnDelete(); // Giáo viên xác nhận
    $table->timestamps();
});
```

#### C. Quy tắc Nghiệp vụ & Kiểm tra trùng lịch (Conflict Detection)
Trước khi lưu một bản ghi lịch (`Schedule` hoặc `ScheduleException`), hệ thống sẽ chạy validation qua `NoConflictScheduleRule`:
1.  **Tránh trùng lịch Giáo viên (`employee_id`)**: Hệ thống truy vấn các buổi học đã đăng ký của giáo viên trong khung giờ dự kiến, đảm bảo không có lịch trùng lắp chéo: `!(new_end_time <= existing_start_time || new_start_time >= existing_end_time)`.
2.  **Tránh trùng lịch Học sinh (`student_id`)**: Đảm bảo trẻ không bị xếp lịch trị liệu cá nhân 1-1 trùng với lớp học nhóm hoặc lịch trị liệu khác cùng giờ.
3.  **Tránh trùng Phòng trị liệu (`equipment_id`)**: Tích hợp kiểm tra trạng thái phòng chức năng/học cụ để đảm bảo phòng không bị đặt đồng thời bởi nhiều giáo viên.

#### D. Trải nghiệm người dùng & Giao diện (Filament UI/UX)
*   **Trang Tổng quan Lịch biểu (Calendar Board / Schedule Overview)**:
    *   Tích hợp package `saade/filament-fullcalendar` để xây dựng một dashboard lịch trực quan và chuyên nghiệp.
    *   **Bộ lọc đa năng (Multi-filter)**: Cho phép người dùng lọc lịch theo **Giáo viên phụ trách**, **Tên học sinh**, **Phòng chức năng**, hoặc **Loại lớp học** (1-1 hoặc nhóm).
    *   **Chế độ hiển thị đa dạng (Calendar Views)**: Hỗ trợ linh hoạt xem theo Tuần (Weekly - chế độ quan trọng nhất đối với lịch can thiệp), theo Ngày (Daily) và theo Tháng (Monthly).
    *   **Màu sắc phân biệt sinh động**: Mỗi khối ca học được tô màu tự động theo Giáo viên phụ trách hoặc theo Loại hoạt động (ví dụ: màu đỏ cho lịch bị hủy/nghỉ học, màu cam/vàng cho lịch có thay đổi/dạy thay, màu xanh cho ca học bình thường).
    *   **Kéo thả trực quan (Drag & Drop)**: Hỗ trợ Admin kéo thả các khối lịch để dời lịch nhanh hoặc đổi giáo viên trực tiếp trên giao diện lịch (hệ thống sẽ tự động cập nhật hoặc tạo mới một `ScheduleException`).
    *   **Xem chi tiết & Thao tác nhanh (Quick Actions Modal)**: Khi click vào một sự kiện trên lịch, hệ thống hiển thị modal pop-up:
        *   Thông tin trẻ (Tên, Nickname, thông tin liên lạc phụ huynh).
        *   Liên kết nhanh tới Mục tiêu IEP hiện hành của ca học đó.
        *   Nút "Điểm danh nhanh" (Quick Check-in) và "Tạo Nhật ký ngày" (Create Daily Log) của ca học đó.
*   **Điểm danh Nhanh qua Mã QR (QR Attendance)**:
    *   Hệ thống sinh mã QR động cho học sinh hoặc cho buổi học.
    *   Giáo viên quét mã QR bằng thiết bị di động để hiển thị form điểm danh nhanh (chỉ cần chọn Trạng thái & ghi chú).
    *   Giao diện điểm danh lưới (Grid check-in) cho lớp học nhóm.
*   **Tích hợp Phân quyền (ACL)**:
    *   *Giáo viên (Teacher)*: Chỉ xem được lịch dạy của mình và học sinh được phân công trên Calendar, thực hiện điểm danh.
    *   *Quản trị viên (Admin/Owner)*: Toàn quyền xếp lịch, điều chỉnh lịch ngoại lệ và xuất báo cáo tổng số giờ can thiệp của toàn trung tâm.

### 4. Phân hệ Cổng thông tin Phụ huynh (Parent Portal / Mobile App)
Cha mẹ của trẻ đặc biệt có nhu cầu đồng hành cực kỳ cao và cần được cập nhật tiến trình hàng ngày.
*   **Sổ liên lạc điện tử**: Phụ huynh nhận báo cáo ngày (Daily Log), xem album ảnh hoạt động, nhận xét của giáo viên can thiệp 1-1.
*   **Bài tập về nhà cá nhân hóa**: Giáo viên gửi các video hướng dẫn hoặc nhiệm vụ đơn giản để phụ huynh cùng thực hành với con ở nhà (ví dụ: các bài tập điều hòa cảm giác, bài tập ngôn ngữ).
*   **Phản hồi & Xin nghỉ phép**: Phụ huynh gửi đơn xin nghỉ phép, phản hồi tình trạng sức khỏe của con trước khi đến lớp.

### 5. Phân hệ Quản lý Tài chính & Học phí theo giờ trị liệu (Billing & Services)
Học phí của trung tâm đặc biệt thường phức tạp vì tính theo gói giờ hoặc số buổi can thiệp 1-1 thực tế.
*   **Quản lý gói dịch vụ can thiệp**: Thiết lập bảng giá theo giờ trị liệu 1-1, học phí bán trú, phí trị liệu nhóm.
*   **Tự động tính học phí cuối tháng**: Dựa trên dữ liệu điểm danh thực tế của hệ thống để cấn trừ các buổi nghỉ có phép/không phép và kết xuất phiếu thu học phí tự động gửi qua email/zalo cho phụ huynh.

### 6. Phân hệ Mở rộng Quản lý Thiết bị & Đặt phòng chức năng (Therapy Room Booking)
Phát triển từ package `equipment` (quản lý học cụ) sẵn có:
*   **Đặt phòng trị liệu**: Đặt lịch sử dụng các phòng đặc thù như *Phòng cảm giác (Sensory Room), Phòng vận động (OT Room), Phòng trị liệu âm nhạc* để tránh giáo viên bị trùng lịch phòng khi dạy trẻ.

---

### Khả năng mở rộng trên cấu trúc hiện tại:
Với cấu trúc module hiện tại của dự án, bạn có thể dễ dàng tạo thêm các package độc lập như:
*   `packages/assessment` (Cho mục 1)
*   `packages/session_log` (Cho mục 2)
*   `packages/scheduler` (Cho mục 3)
*   `packages/billing` (Cho mục 5)

Kiến trúc này giúp dự án giữ được sự sạch sẽ, dễ bảo trì, dễ nâng cấp và có thể bật/tắt các tính năng này cho từng chi nhánh/trung tâm khác nhau khi cần thiết.
