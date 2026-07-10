<?php

namespace Quochao56\SessionLog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Quochao56\Employee\Models\Employee;
use Quochao56\SessionLog\Filament\Enums\BehaviorIntensityEnum;
use Quochao56\SessionLog\Filament\Enums\DailyLogEmotionEnum;
use Quochao56\SessionLog\Filament\Enums\DailyLogRatingEnum;
use Quochao56\SessionLog\Filament\Enums\DailyLogStatusEnum;
use Quochao56\SessionLog\Models\BehaviorIncident;
use Quochao56\SessionLog\Models\DailyLog;
use Quochao56\Student\Models\Student;

class SessionLogSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy danh sách học sinh và giáo viên đã có từ PlanningEvaluationSeeder
        $students = Student::all();
        $employees = Employee::all();

        if ($students->isEmpty() || $employees->isEmpty()) {
            // Nếu chưa có dữ liệu học sinh/giáo viên, tạo nhanh dữ liệu giả định
            $student = Student::firstOrCreate(
                ['student_code' => 'HS001'],
                [
                    'name' => 'Nguyễn Hoàng Nam',
                    'nickname' => 'Nam',
                    'gender' => 'male',
                    'dob' => Carbon::parse('2020-05-15'),
                    'status' => 'active',
                ]
            );
            $students = collect([$student]);

            $employee = Employee::firstOrCreate(
                ['employee_code' => 'NV001'],
                [
                    'name' => 'Nguyễn Văn A',
                    'email' => 'teacher1@example.com',
                    'position' => 'Giáo viên',
                    'status' => 'active',
                ]
            );
            $employees = collect([$employee]);
        }

        $teacher = $employees->first();

        // 1. Dữ liệu cho Trẻ 3 tuổi (Ví dụ: Nguyễn Hoàng Nam - Chậm nói, tự kỷ)
        $nam = $students->where('student_code', 'HS001')->first() ?? $students->first();
        DailyLog::firstOrCreate(
            [
                'student_id' => $nam->id,
                'log_date' => Carbon::now()->subDays(2)->toDateString(),
            ],
            [
                'employee_id' => $teacher->id,
                'emotion' => DailyLogEmotionEnum::Normal->value,
                'focus_level' => DailyLogRatingEnum::Poor->value,
                'cooperation_level' => DailyLogRatingEnum::Poor->value,
                'eating_note' => 'Ăn chậm, nhai nuốt chưa tốt, cần giáo viên hỗ trợ đút.',
                'sleeping_note' => 'Khó vào giấc ngủ trưa, trằn trọc khoảng 30 phút, giật mình tỉnh dậy giữa chừng khóc tìm cô.',
                'hygiene_note' => 'Chưa chủ động gọi khi buồn vệ sinh, vẫn dùng bỉm hoàn toàn.',
                'general_note' => 'Trẻ có phản ứng quay lại khi gọi tên nhưng chưa giao tiếp mắt tốt, thời gian tập trung bài học ngắn.',
                'status' => DailyLogStatusEnum::Completed->value,
            ]
        );

        BehaviorIncident::create([
            'student_id' => $nam->id,
            'employee_id' => $teacher->id,
            'incident_date' => Carbon::now()->subDays(2)->setTime(10, 15, 0),
            'antecedent' => 'Giáo viên cất các thẻ hình khối lắp ráp lego để chuyển sang giờ học nhóm ngôn ngữ phát âm.',
            'behavior' => 'Trẻ lập tức hét lớn, khóc lóc, dùng trán đập mạnh xuống sàn thảm xốp và lấy tay tự cào má mình gây xước nhẹ.',
            'consequence' => 'Giáo viên ôm nhẹ trẻ từ phía sau (deep pressure) để bảo vệ trẻ khỏi tự gây thương tích, thì thầm giọng dịu dàng trấn an: "Cô biết con muốn lắp hình, nhưng bây giờ chúng ta cất đi nhé". Sau 5 phút trẻ bình tĩnh lại và hợp tác chuyển góc học.',
            'duration_minutes' => 5,
            'intensity' => BehaviorIntensityEnum::High->value,
            'notes' => 'Hành vi phản kháng khi chuyển đổi hoạt động (transition) xảy ra thường xuyên. Cần chuẩn bị thẻ hình ảnh báo trước (Visual Schedule) trước 3 phút.',
        ]);

        // 2. Dữ liệu cho Trẻ 5 tuổi (Ví dụ: Trần Bảo Vy - Tăng động giảm chú ý ADHD)
        $vy = $students->where('student_code', 'HS002')->first() ?? $students->first();
        DailyLog::firstOrCreate(
            [
                'student_id' => $vy->id,
                'log_date' => Carbon::now()->subDays(1)->toDateString(),
            ],
            [
                'employee_id' => $teacher->id,
                'emotion' => DailyLogEmotionEnum::Hyperactive->value,
                'focus_level' => DailyLogRatingEnum::Poor->value,
                'cooperation_level' => DailyLogRatingEnum::Normal->value,
                'eating_note' => 'Ăn rất nhanh, hay ngọ nguậy chân tay làm đổ cơm vãi ra bàn, không chịu ngồi yên một chỗ.',
                'sleeping_note' => 'Ngủ trưa 1 tiếng, trước khi ngủ lăn lộn nhiều vòng và trêu chọc các bạn nằm cạnh.',
                'hygiene_note' => 'Đã biết gọi khi đi vệ sinh, tự kéo quần dưới sự nhắc nhở của cô giáo.',
                'general_note' => 'Hôm nay thừa năng lượng, vận động thô nhiều, leo trèo các thiết bị trong lớp học.',
                'status' => DailyLogStatusEnum::Completed->value,
            ]
        );

        BehaviorIncident::create([
            'student_id' => $vy->id,
            'employee_id' => $teacher->id,
            'incident_date' => Carbon::now()->subDays(1)->setTime(14, 30, 0),
            'antecedent' => 'Yêu cầu trẻ ngồi yên trên ghế để thực hiện bài tập xâu chuỗi hạt vòng gỗ liên tục trong 10 phút.',
            'behavior' => 'Trẻ liên tục nhấp nhổm đứng lên, ném hạt vòng gỗ khắp phòng, la hét và đẩy mạnh khay học cụ ra khỏi bàn khi cô yêu cầu ngồi lại.',
            'consequence' => 'Giáo viên cho trẻ tạm nghỉ 2 phút để nhảy lò cò trên thảm số điều hòa cảm giác vận động, sau đó chia nhỏ bài học thành các khoảng 3 phút rồi thưởng sticker. Trẻ hoàn thành tốt sau đó.',
            'duration_minutes' => 8,
            'intensity' => BehaviorIntensityEnum::Mild->value,
            'notes' => 'Trẻ ADHD cần các quãng nghỉ vận động ngắn (brain breaks) giữa giờ học để giải phóng năng lượng dư thừa.',
        ]);

        // 3. Dữ liệu cho Trẻ 7 tuổi (Ví dụ: Lê Minh Triết - Tự kỷ chức năng cao / Asperger)
        $triet = $students->where('student_code', 'HS003')->first() ?? $students->first();
        DailyLog::firstOrCreate(
            [
                'student_id' => $triet->id,
                'log_date' => Carbon::now()->toDateString(),
            ],
            [
                'employee_id' => $teacher->id,
                'emotion' => DailyLogEmotionEnum::Happy->value,
                'focus_level' => DailyLogRatingEnum::Good->value,
                'cooperation_level' => DailyLogRatingEnum::Good->value,
                'eating_note' => 'Tự xúc ăn ngoan, dọn khay ăn sạch sẽ đúng nơi quy định.',
                'sleeping_note' => 'Tự giác lên giường nằm, ngủ sâu giấc và đúng giờ.',
                'hygiene_note' => 'Hoàn toàn độc lập trong vệ sinh cá nhân.',
                'general_note' => 'Trẻ hoàn thành xuất sắc mục tiêu học nhận biết các từ vựng chủ đề động vật hoang dã ngày hôm nay.',
                'status' => DailyLogStatusEnum::Completed->value,
            ]
        );

        BehaviorIncident::create([
            'student_id' => $triet->id,
            'employee_id' => $teacher->id,
            'incident_date' => Carbon::now()->setTime(10, 45, 0),
            'antecedent' => 'Trong giờ chơi tự do ở phòng đa năng, một bạn học sinh khác vô tình va vào làm đổ tháp gỗ mô hình mà trẻ đã tỉ mỉ lắp ghép suốt 20 phút.',
            'behavior' => 'Trẻ lập tức khóc ré lên dữ dội, lao vào cắn mạnh vào tay bạn kia, giật tóc bạn và dùng chân đạp đổ toàn bộ đồ chơi xung quanh phòng.',
            'consequence' => 'Giáo viên can thiệp tách ngay hai trẻ, hỗ trợ sơ cứu vết cắn của bạn học. Đưa trẻ Triết vào góc lắng dịu (calming corner) để nghe nhạc nhẹ và thực hiện kỹ thuật thở sâu bằng bong bóng. Khi bình tĩnh, hướng dẫn trẻ dùng lời nói thay vì cắn bạn.',
            'duration_minutes' => 15,
            'intensity' => BehaviorIntensityEnum::Severe->value,
            'notes' => 'Hành vi bùng nổ (meltdown) khi gặp khủng hoảng cảm xúc. Cần hướng dẫn trẻ kỹ năng kiểm soát cơn giận bằng hình ảnh trực quan.',
        ]);
    }
}
