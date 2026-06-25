# Kế Hoạch Phát Triển Các Tính Năng Tiếp Theo (Dự Án Bước Chân Nhỏ)

Tài liệu này ghi lại các tính năng đề xuất tiếp theo cho dự án **buocchannho**, phân chia theo các phân hệ/package nghiệp vụ hiện có.

---

## 1. Phân hệ Kế hoạch & Đánh giá (`planning_evaluation`)

### 1.1 Giao diện Lịch sử chỉnh sửa (Plan/Evaluation History UI)
* **Mô tả:** Hiển thị và so sánh các phiên bản thay đổi của kế hoạch và đánh giá.
* **Hiện trạng:** Đã cấu trúc sẵn các bảng `planning_histories` và `evaluation_histories` tự động lưu snapshot mỗi khi lưu (`saved` Eloquent event). Chưa có giao diện để người dùng xem lại.
* **Giải pháp đề xuất:**
  * Thêm một tab hoặc một Section/Relation Manager hiển thị lịch sử ở trang Edit của Planning và Evaluation.
  * Hiển thị người thực hiện chỉnh sửa (`saved_by`), thời điểm lưu, và cho phép so sánh sự khác biệt (diff) giữa các snapshot.

### 1.2 Nhân bản kế hoạch dạy học (Clone / Duplicate Plan)
* **Mô tả:** Cho phép sao chép nhanh một kế hoạch dạy học đã có sang cho trẻ khác hoặc kỳ học mới.
* **Lý do:** Tiết kiệm thời gian soạn kế hoạch cho giáo viên đối với các bé có cùng độ tuổi hoặc mức độ nhận thức tương tự.
* **Giải pháp đề xuất:**
  * Tạo một Action tùy chỉnh `Clone` ở danh sách kế hoạch hoặc trang chi tiết.
  * Khi click, hệ thống tự động nhân bản toàn bộ thông tin chi tiết của kế hoạch (`planning_details`), giáo viên chỉ cần chọn học sinh mới và điều chỉnh lại thời gian học.

### 1.3 Dashboard Theo dõi tiến bộ của Trẻ (Progress Analytics Dashboard)
* **Mô tả:** Biểu đồ trực quan hóa quá trình và kết quả học tập của trẻ qua các kỳ đánh giá.
* **Giải pháp đề xuất:**
  * Sử dụng Filament Widgets tích hợp Chart.js để vẽ các biểu đồ (Line/Bar Chart) thống kê tỷ lệ hoàn thành mục tiêu (`+`, `+/-`, `-`) qua các đợt đánh giá định kỳ của từng trẻ.

---

## 2. Phân hệ Học cụ & Thiết bị dạy học (`equipment`)

### 2.1 Nhập dữ liệu học cụ từ file Excel (Import Equipment)
* **Mô tả:** Cho phép quản trị viên nhập hàng loạt học cụ nhanh chóng qua file excel thay vì tạo thủ công từng cái.
* **Hiện trạng:** Đã có tính năng xuất Excel (`EquipmentExcelExport`).
* **Giải pháp đề xuất:**
  * Tích hợp tính năng Import sử dụng thư viện Laravel Excel hoặc Spatie Simple Excel.
  * Cung cấp file Excel mẫu (template) để tải về và tải lên.

### 2.2 Quản lý Mượn/Trả học cụ (Equipment Loan & Return Management)
* **Mô tả:** Theo dõi việc giáo viên mượn học cụ ra khỏi kho phục vụ tiết dạy và trả lại sau khi hoàn thành.
* **Giải pháp đề xuất:**
  * Tạo mới Model & Filament Resource `EquipmentLoan` liên kết với Giáo viên (`Employee`) và Học cụ (`Equipment`).
  * Các trường thông tin cần thiết: Người mượn, Học cụ, Số lượng mượn, Ngày mượn, Ngày dự kiến trả, Trạng thái (Đang mượn, Đã trả, Trễ hẹn).

### 2.3 Cảnh báo Tự động (Alerts & Notifications)
* **Mô tả:** Cảnh báo khi có thay đổi bất thường về số lượng hoặc trạng thái học cụ.
* **Giải pháp đề xuất:**
  * Tự động gửi thông báo hệ thống (Filament Database Notifications) khi:
    * Số lượng thực tế của học cụ sau khi duyệt kiểm kê (`Approved` inventory) bị thiếu hụt dưới mức tối thiểu (Min Threshold).
    * Có thiết bị dạy học bị ghi nhận là "Hỏng" hoặc "Mất tích" trong quá trình kiểm kê.

---

## 3. Phân hệ Học sinh & Lớp học (`student` & `employee`)

### 3.1 Quản lý Lớp học / Nhóm học (Classroom Management)
* **Mô tả:** Phân chia học sinh vào các lớp/nhóm cụ thể và gán giáo viên phụ trách chuyên môn.
* **Giải pháp đề xuất:**
  * Tạo mới model/resource `Classroom` hoặc `ClassGroup` làm cầu nối liên kết mối quan hệ nhiều-nhiều hoặc một-nhiều giữa Học sinh (`Student`) và Nhân viên (`Employee`).

### 3.2 Nhật ký Hoạt động Hàng ngày (Daily Student Log)
* **Mô tả:** Giáo viên điểm danh và ghi chép nhanh tình trạng thể chất/tinh thần của trẻ hàng ngày.
* **Giải pháp đề xuất:**
  * Tạo Resource `DailyLog` liên kết trực tiếp với Học sinh.
  * Hỗ trợ giáo viên đánh giá nhanh các trạng thái: Chuyên cần (Có mặt/Vắng), Ăn uống, Ngủ nghỉ, Sức khỏe và Biểu hiện đặc biệt trong ngày để lưu giữ hồ sơ và tương tác với phụ huynh.
