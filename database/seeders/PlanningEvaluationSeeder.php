<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Models\Evaluation;
use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\PlanningEvaluation\Models\StudentAssignment;
use Quochao56\Student\Models\Student;

class PlanningEvaluationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo hoặc lấy User/Employee cho tài khoản Đăng Nhập (test@example.com)
        $adminUser = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );

        $adminEmployee = Employee::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'employee_code' => 'NV000',
                'name' => 'Admin Teacher',
                'phone' => '0901234567',
                'address' => 'Hồ Chí Minh',
                'position' => 'Giáo viên chủ nhiệm',
                'employment_type' => 'full-time',
                'hired_at' => Carbon::now()->subYears(2),
                'status' => BaseStatusEnum::Active,
                'dob' => Carbon::parse('1990-01-01'),
                'gender' => 'female',
            ]
        );

        // 2. Tạo thêm Giáo viên phụ trách khác
        $teacher1User = User::query()->firstOrCreate(
            ['email' => 'teacher1@example.com'],
            [
                'name' => 'Nguyễn Văn A',
                'password' => bcrypt('password'),
            ]
        );

        $teacher1 = Employee::query()->firstOrCreate(
            ['email' => 'teacher1@example.com'],
            [
                'employee_code' => 'NV001',
                'name' => 'Nguyễn Văn A',
                'phone' => '0911223344',
                'address' => 'Hà Nội',
                'position' => 'Giáo viên',
                'employment_type' => 'full-time',
                'hired_at' => Carbon::now()->subYear(),
                'status' => BaseStatusEnum::Active,
                'dob' => Carbon::parse('1992-05-10'),
                'gender' => 'male',
            ]
        );

        $teacher2User = User::query()->firstOrCreate(
            ['email' => 'teacher2@example.com'],
            [
                'name' => 'Trần Thị B',
                'password' => bcrypt('password'),
            ]
        );

        $teacher2 = Employee::query()->firstOrCreate(
            ['email' => 'teacher2@example.com'],
            [
                'employee_code' => 'NV002',
                'name' => 'Trần Thị B',
                'phone' => '0988776655',
                'address' => 'Đà Nẵng',
                'position' => 'Giáo viên can thiệp',
                'employment_type' => 'part-time',
                'hired_at' => Carbon::now()->subMonths(6),
                'status' => BaseStatusEnum::Active,
                'dob' => Carbon::parse('1995-08-20'),
                'gender' => 'female',
            ]
        );

        // 3. Tạo Học sinh
        $studentsData = [
            [
                'student_code' => 'HS001',
                'name' => 'Nguyễn Hoàng Nam',
                'nickname' => 'Nam',
                'gender' => 'male',
                'dob' => Carbon::parse('2020-05-15'),
                'father_name' => 'Nguyễn Hoàng Hải',
                'father_phone' => '090111222',
                'mother_name' => 'Lê Thị Mai',
                'mother_phone' => '090333444',
                'status' => 'active',
            ],
            [
                'student_code' => 'HS002',
                'name' => 'Trần Bảo Vy',
                'nickname' => 'Vy',
                'gender' => 'female',
                'dob' => Carbon::parse('2021-02-20'),
                'father_name' => 'Trần Quốc Bảo',
                'father_phone' => '091222333',
                'mother_name' => 'Phạm Thu Thảo',
                'mother_phone' => '091444555',
                'status' => 'active',
            ],
            [
                'student_code' => 'HS003',
                'name' => 'Lê Minh Triết',
                'nickname' => 'Triết',
                'gender' => 'male',
                'dob' => Carbon::parse('2019-11-10'),
                'father_name' => 'Lê Minh Hùng',
                'father_phone' => '092333444',
                'mother_name' => 'Trần Kim Chi',
                'mother_phone' => '092555666',
                'status' => 'active',
            ],
            [
                'student_code' => 'HS004',
                'name' => 'Phạm Hải Đăng',
                'nickname' => 'Đăng',
                'gender' => 'male',
                'dob' => Carbon::parse('2020-08-05'),
                'father_name' => 'Phạm Văn Dũng',
                'father_phone' => '093444555',
                'mother_name' => 'Đỗ Thị Ngọc',
                'mother_phone' => '093666777',
                'status' => 'active',
            ],
            [
                'student_code' => 'HS005',
                'name' => 'Hoàng Gia Bảo',
                'nickname' => 'Bảo',
                'gender' => 'male',
                'dob' => Carbon::parse('2020-12-25'),
                'father_name' => 'Hoàng Minh Tuấn',
                'father_phone' => '094555666',
                'mother_name' => 'Nguyễn Thị Hồng',
                'mother_phone' => '094777888',
                'status' => 'active',
            ],
        ];

        $students = [];
        foreach ($studentsData as $data) {
            $students[] = Student::query()->firstOrCreate(
                ['student_code' => $data['student_code']],
                $data
            );
        }

        // 4. Phân công Giáo viên phụ trách (StudentAssignment)
        // HS001 -> Admin Teacher (test@example.com), gán từ 3 tháng trước
        StudentAssignment::query()->firstOrCreate(
            ['student_id' => $students[0]->id, 'unassigned_at' => null],
            [
                'employee_id' => $adminEmployee->id,
                'assigned_at' => Carbon::now()->subMonths(3),
            ]
        );

        // HS002 -> Admin Teacher, gán từ 2 tháng trước
        StudentAssignment::query()->firstOrCreate(
            ['student_id' => $students[1]->id, 'unassigned_at' => null],
            [
                'employee_id' => $adminEmployee->id,
                'assigned_at' => Carbon::now()->subMonths(2),
            ]
        );

        // HS003 -> Nguyễn Văn A, gán từ 1 tháng trước
        StudentAssignment::query()->firstOrCreate(
            ['student_id' => $students[2]->id, 'unassigned_at' => null],
            [
                'employee_id' => $teacher1->id,
                'assigned_at' => Carbon::now()->subMonth(),
            ]
        );

        // HS004 -> Trần Thị B, gán từ 4 tháng trước
        StudentAssignment::query()->firstOrCreate(
            ['student_id' => $students[3]->id, 'unassigned_at' => null],
            [
                'employee_id' => $teacher2->id,
                'assigned_at' => Carbon::now()->subMonths(4),
            ]
        );

        // HS005 -> Lịch sử gán:
        // - Đầu tiên gán cho Nguyễn Văn A (Từ 6 tháng trước -> 3 tháng trước)
        // - Sau đó gán cho Admin Teacher (Từ 3 tháng trước -> nay)
        $hasAssignment = StudentAssignment::query()->where('student_id', $students[4]->id)->exists();
        if (! $hasAssignment) {
            StudentAssignment::query()->create([
                'student_id' => $students[4]->id,
                'employee_id' => $teacher1->id,
                'assigned_at' => Carbon::now()->subMonths(6),
                'unassigned_at' => Carbon::now()->subMonths(3),
            ]);

            StudentAssignment::query()->create([
                'student_id' => $students[4]->id,
                'employee_id' => $adminEmployee->id,
                'assigned_at' => Carbon::now()->subMonths(3),
                'unassigned_at' => null,
            ]);
        }

        // 5. Tạo dữ liệu Kế hoạch & Đánh giá mẫu cho Nguyễn Hoàng Nam (HS001) để vẽ biểu đồ tiến độ
        $planningDetails = [
            [
                'linh_vuc' => [
                    ['content' => '**Kỹ năng tiền đề**'],
                    ['content' => '- Chú ý'],
                ],
                'muc_tieu' => [
                    ['content' => 'Tập trung chú ý khi cô gọi tên'],
                    ['content' => 'Duy trì giao tiếp mắt 3-5 giây'],
                ],
                'hoat_dong' => [],
                'phuong_tien' => [],
                'muc_tieu_du_phong' => [],
            ],
            [
                'linh_vuc' => [
                    ['content' => '**Ngôn ngữ và giao tiếp**'],
                    ['content' => '- Ngôn ngữ tiếp nhận'],
                ],
                'muc_tieu' => [
                    ['content' => 'Bắt chước âm nguyên âm o, a'],
                    ['content' => 'Nói từ đơn lẻ để yêu cầu đồ chơi'],
                ],
                'hoat_dong' => [],
                'phuong_tien' => [],
                'muc_tieu_du_phong' => [],
            ],
        ];

        // --- THÁNG 1 ---
        // Lập kế hoạch Tháng 1
        $planningJan = Planning::query()->firstOrCreate(
            ['student_id' => $students[0]->id, 'name' => 'Kế hoạch học tập Tháng 01/2026'],
            [
                'description' => 'Kế hoạch rèn luyện chú ý và bật âm cho Nam trong tháng 01/2026',
                'employee_id' => $adminEmployee->id,
                'start_date' => Carbon::parse('2026-01-01'),
                'end_date' => Carbon::parse('2026-01-31'),
                'planning_details' => $planningDetails,
                'status' => BaseStatusEnum::Published,
            ]
        );

        // Cập nhật ngày tạo của PlanningJan về tháng 1
        $planningJan->created_at = Carbon::parse('2026-01-05 08:00:00');
        $planningJan->save();

        // Tạo đánh giá Tháng 1 (Chưa tiến bộ nhiều)
        $evaluationJanDetails = [
            [
                'linh_vuc' => "**Kỹ năng tiền đề**\n- Chú ý",
                'muc_tieu' => [
                    ['content' => 'Tập trung chú ý khi cô gọi tên', 'danh_gia' => '-', 'nhan_xet' => 'Chưa phản ứng khi gọi'],
                    ['content' => 'Duy trì giao tiếp mắt 3-5 giây', 'danh_gia' => '+/-', 'nhan_xet' => 'Chỉ nhìn được khoảng 1-2 giây'],
                ],
            ],
            [
                'linh_vuc' => "**Ngôn ngữ và giao tiếp**\n- Ngôn ngữ tiếp nhận",
                'muc_tieu' => [
                    ['content' => 'Bắt chước âm nguyên âm o, a', 'danh_gia' => '-', 'nhan_xet' => 'Chưa tự phát âm được'],
                    ['content' => 'Nói từ đơn lẻ để yêu cầu đồ chơi', 'danh_gia' => '-', 'nhan_xet' => 'Vẫn dùng cử chỉ kéo tay'],
                ],
            ],
        ];

        $evalJan = Evaluation::query()->firstOrCreate(
            ['planning_id' => $planningJan->id],
            [
                'name' => 'Đánh giá Kế hoạch Tháng 01/2026',
                'description' => 'Đánh giá tiến độ rèn luyện cuối tháng 1',
                'evaluation_details' => $evaluationJanDetails,
                'status' => BaseStatusEnum::Published,
            ]
        );
        $evalJan->created_at = Carbon::parse('2026-01-31 16:00:00');
        $evalJan->save();

        // --- THÁNG 2 ---
        // Lập kế hoạch Tháng 2
        $planningFeb = Planning::query()->firstOrCreate(
            ['student_id' => $students[0]->id, 'name' => 'Kế hoạch học tập Tháng 02/2026'],
            [
                'description' => 'Kế hoạch tiếp tục duy trì và tăng cường tương tác trong tháng 2',
                'employee_id' => $adminEmployee->id,
                'start_date' => Carbon::parse('2026-02-01'),
                'end_date' => Carbon::parse('2026-02-28'),
                'planning_details' => $planningDetails,
                'status' => BaseStatusEnum::Published,
            ]
        );
        $planningFeb->created_at = Carbon::parse('2026-02-03 08:00:00');
        $planningFeb->save();

        // Đánh giá Tháng 2 (Bắt đầu tiến bộ)
        $evaluationFebDetails = [
            [
                'linh_vuc' => "**Kỹ năng tiền đề**\n- Chú ý",
                'muc_tieu' => [
                    ['content' => 'Tập trung chú ý khi cô gọi tên', 'danh_gia' => '+/-', 'nhan_xet' => 'Đã quay đầu lại khi gọi 3/5 lần'],
                    ['content' => 'Duy trì giao tiếp mắt 3-5 giây', 'danh_gia' => '+', 'nhan_xet' => 'Nhìn mắt cô tốt khi giao tiếp trò chơi'],
                ],
            ],
            [
                'linh_vuc' => "**Ngôn ngữ và giao tiếp**\n- Ngôn ngữ tiếp nhận",
                'muc_tieu' => [
                    ['content' => 'Bắt chước âm nguyên âm o, a', 'danh_gia' => '+/-', 'nhan_xet' => 'Phát âm được âm a, o còn hơi méo'],
                    ['content' => 'Nói từ đơn lẻ để yêu cầu đồ chơi', 'danh_gia' => '-', 'nhan_xet' => 'Vẫn cần nhắc nhở nhiều'],
                ],
            ],
        ];

        $evalFeb = Evaluation::query()->firstOrCreate(
            ['planning_id' => $planningFeb->id],
            [
                'name' => 'Đánh giá Kế hoạch Tháng 02/2026',
                'description' => 'Đánh giá tiến độ rèn luyện cuối tháng 2',
                'evaluation_details' => $evaluationFebDetails,
                'status' => BaseStatusEnum::Published,
            ]
        );
        $evalFeb->created_at = Carbon::parse('2026-02-28 16:00:00');
        $evalFeb->save();

        // --- THÁNG 3 ---
        // Lập kế hoạch Tháng 3
        $planningMar = Planning::query()->firstOrCreate(
            ['student_id' => $students[0]->id, 'name' => 'Kế hoạch học tập Tháng 03/2026'],
            [
                'description' => 'Kế hoạch nâng cao giao tiếp và ngôn ngữ nói tháng 3',
                'employee_id' => $adminEmployee->id,
                'start_date' => Carbon::parse('2026-03-01'),
                'end_date' => Carbon::parse('2026-03-31'),
                'planning_details' => $planningDetails,
                'status' => BaseStatusEnum::Published,
            ]
        );
        $planningMar->created_at = Carbon::parse('2026-03-02 08:00:00');
        $planningMar->save();

        // Đánh giá Tháng 3 (Đạt kết quả rất tốt)
        $evaluationMarDetails = [
            [
                'linh_vuc' => "**Kỹ năng tiền đề**\n- Chú ý",
                'muc_tieu' => [
                    ['content' => 'Tập trung chú ý khi cô gọi tên', 'danh_gia' => '+', 'nhan_xet' => 'Quay đầu lại phản hồi lập tức khi gọi'],
                    ['content' => 'Duy trì giao tiếp mắt 3-5 giây', 'danh_gia' => '+', 'nhan_xet' => 'Giao tiếp mắt tự nhiên và ổn định'],
                ],
            ],
            [
                'linh_vuc' => "**Ngôn ngữ và giao tiếp**\n- Ngôn ngữ tiếp nhận",
                'muc_tieu' => [
                    ['content' => 'Bắt chước âm nguyên âm o, a', 'danh_gia' => '+', 'nhan_xet' => 'Bật âm o, a rất rõ ràng, bắt chước nhanh'],
                    ['content' => 'Nói từ đơn lẻ để yêu cầu đồ chơi', 'danh_gia' => '+/-', 'nhan_xet' => 'Nói được từ xe, bóng khi được gợi ý từ đầu'],
                ],
            ],
        ];

        $evalMar = Evaluation::query()->firstOrCreate(
            ['planning_id' => $planningMar->id],
            [
                'name' => 'Đánh giá Kế hoạch Tháng 03/2026',
                'description' => 'Đánh giá tiến độ rèn luyện cuối tháng 3',
                'evaluation_details' => $evaluationMarDetails,
                'status' => BaseStatusEnum::Published,
            ]
        );
        $evalMar->created_at = Carbon::parse('2026-03-31 16:00:00');
        $evalMar->save();

        // 6. Tạo dữ liệu kế hoạch/đánh giá ở dạng Nháp (Draft) hoặc Đang chờ (Pending) cho các học sinh khác
        // Trần Bảo Vy (HS002) - Đang học, có kế hoạch nhưng chưa được đánh giá (Evaluation ở trạng thái Draft)
        $planningVy = Planning::query()->firstOrCreate(
            ['student_id' => $students[1]->id, 'name' => 'Kế hoạch học tập Q1/2026 - Bảo Vy'],
            [
                'description' => 'Kế hoạch hòa nhập quý 1 của Vy',
                'employee_id' => $adminEmployee->id,
                'start_date' => Carbon::parse('2026-01-01'),
                'end_date' => Carbon::parse('2026-03-31'),
                'planning_details' => $planningDetails,
                'status' => BaseStatusEnum::Published,
            ]
        );

        Evaluation::query()->firstOrCreate(
            ['planning_id' => $planningVy->id],
            [
                'name' => 'Đánh giá Kế hoạch Q1/2026 - Bảo Vy',
                'description' => 'Bản nháp đánh giá tiến độ',
                'evaluation_details' => collect($planningDetails)->map(function ($row) {
                    return [
                        'linh_vuc' => implode("\n", collect($row['linh_vuc'])->pluck('content')->all()),
                        'muc_tieu' => collect($row['muc_tieu'])->map(function ($g) {
                            return [
                                'content' => $g['content'],
                                'danh_gia' => null,
                                'nhan_xet' => null,
                            ];
                        })->all(),
                    ];
                })->all(),
                'status' => BaseStatusEnum::Draft,
            ]
        );

        // Lê Minh Triết (HS003) - Do Giáo viên Nguyễn Văn A phụ trách, Kế hoạch Nháp
        Planning::query()->firstOrCreate(
            ['student_id' => $students[2]->id, 'name' => 'Kế hoạch Tháng 03/2026 (Nháp) - Minh Triết'],
            [
                'description' => 'Bản thảo kế hoạch học tập của Triết',
                'employee_id' => $teacher1->id,
                'start_date' => Carbon::parse('2026-03-01'),
                'end_date' => Carbon::parse('2026-03-31'),
                'planning_details' => $planningDetails,
                'status' => BaseStatusEnum::Draft,
            ]
        );
    }
}
