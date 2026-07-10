# Hướng Dẫn Xử Lý Trường Hợp Dạy Đột Xuất (Trường hợp 3)

> Áp dụng khi: Quản lý gọi giáo viên dạy ngay lập tức chưa kịp lên lịch, hoặc giáo viên dạy thay mà chưa kịp tạo điều chỉnh / chưa được duyệt.

---

## Tình huống A — Quản lý gọi giáo viên dạy ngay, không có lịch trước

### Trong ngày xảy ra (trước khi giáo viên vào dạy)

```
1. Quản lý mở Lịch học → chọn lịch học cần điều chỉnh
2. Tab "Danh sách điều chỉnh" → Tạo mới điều chỉnh
   - Hình thức: "Đổi giáo viên (Dạy thay)"
   - Ngày phát sinh: ngày hôm nay
   - Giáo viên dạy thay: chọn giáo viên được gọi
   - Lý do: "Đột xuất - quản lý chỉ định"
3. Lưu → tự động Approved (vì quản lý có quyền duyệt)
4. Giáo viên check-in bình thường → hệ thống nhận diện đúng lịch
```

> [!TIP]
> Nếu chưa có lịch học nào cho giáo viên này (buổi hoàn toàn mới): Vào **Quản lý lịch học → Tạo lịch nhanh** → chọn đúng giáo viên và thời gian. Sau khi tạo, giáo viên có thể check-in và hệ thống gắn đúng lịch.

---

## Tình huống B — Giáo viên dạy thay đột xuất, chưa có điều chỉnh hoặc chưa được duyệt

### Giáo viên đã vào dạy, chấm công ra sao?

| Ca học | Kết quả chấm công | Hành động cần làm |
|--------|------------------|-------------------|
| Ca sáng (08:00–12:00) | ✅ Tính giờ hành chính bình thường | Không cần làm gì thêm |
| Ca chiều (13:30–17:30) | ✅ Tính giờ hành chính bình thường | Không cần làm gì thêm |
| Ca tối (≥18:00) | ⚠️ `total_hours = 0` nếu không có lịch được duyệt | **Cần sửa công** (xem bên dưới) |

### Sửa công sau khi sự việc đã xảy ra

**Cách 1 (Khuyến nghị — Nhanh nhất):** Admin tạo Yêu cầu sửa công thay cho giáo viên

```
1. Mở Chấm công → Yêu cầu sửa công → Tạo mới
2. Chọn giáo viên và ngày xảy ra
3. Nhập giờ check-in thực tế và giờ check-out thực tế
4. Lý do: "Dạy thay đột xuất ca tối ngày [ngày]"
5. Lưu → Admin duyệt ngay (hoặc tự duyệt nếu có quyền)
6. Hệ thống tự cập nhật bản ghi chấm công với giờ đúng
```

**Cách 2:** Admin vào tab Chấm công, sửa trực tiếp `total_hours` cho bản ghi ca tối của giáo viên đó.

> [!IMPORTANT]
> **Sau khi sự việc xảy ra**, quản lý vẫn nên tạo `ScheduleException` (dù muộn) để dữ liệu lịch học được ghi nhận chính xác. Điều này giúp báo cáo tải giáo viên và thống kê ca học sau này không bị sai.

---

## Tình huống C — Giáo viên tự tạo điều chỉnh nhưng chưa được duyệt

### Trạng thái hệ thống

```
ScheduleException.status = 'pending'
→ Không ảnh hưởng đến lịch học hay chấm công
→ Giáo viên check-in: hệ thống KHÔNG nhận diện là dạy thay
→ Bản ghi chấm công được tạo như bình thường (gắn lịch gốc nếu trùng giờ)
```

### Quy trình xử lý

```
[Giáo viên]                    [Quản lý]
     │                              │
     ▼                              │
  Tạo điều chỉnh               Nhận thông báo
  (status = pending)           "Có điều chỉnh chờ duyệt"
     │                              │
     │                              ▼
     │                        Vào Lịch học →
     │                        Mở lịch → Tab Điều chỉnh
     │                              │
     │                        Xem xét yêu cầu:
     │                        ├─ Duyệt → status = approved
     │                        │   → Áp dụng ngay vào lịch + chấm công
     │                        └─ Từ chối → nhập lý do
     │                              │
     ▼                              ▼
  [Nếu đã check-in trước khi được duyệt]
     → Chấm công đã ghi theo lịch cũ
     → Cần Admin tạo Yêu cầu sửa công để điều chỉnh
```

---

## Bảng quyết định nhanh

| Tình huống | Ai làm | Làm gì | Kết quả |
|-----------|--------|--------|---------|
| Quản lý gọi đột xuất, còn thời gian | Quản lý | Tạo ScheduleException (substitute) → tự approved | Giáo viên check-in nhận diện đúng |
| Quản lý gọi đột xuất, không kịp làm gì | Giáo viên check-in bình thường | Quản lý sửa sau | Ca ngày OK; ca tối cần sửa công |
| Giáo viên nhớ dạy thay, tạo điều chỉnh | Giáo viên tạo → pending | Quản lý duyệt sớm trước khi GV check-in | Nhận diện đúng nếu duyệt kịp |
| Tất cả đã xảy ra, cần sửa | Admin | Tạo AttendanceCorrectionRequest | Giờ công được cập nhật đúng |

---

## Lưu ý quan trọng

> [!CAUTION]
> Ca tối bị mất giờ nếu không có lịch được duyệt. Quản lý cần **duyệt điều chỉnh TRƯỚC khi giáo viên check-out ca tối** để tránh phải sửa công sau.

> [!NOTE]
> Ca sáng và ca chiều (giờ hành chính) không bị ảnh hưởng bởi việc có/không có lịch. Giờ công được tính theo giờ thực tế check-in/out.
