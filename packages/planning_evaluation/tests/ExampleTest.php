<?php

use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Filament\Actions\ExportEvaluationWordAction;
use Quochao56\PlanningEvaluation\Filament\Actions\ExportPlanningWordAction;
use Quochao56\PlanningEvaluation\Models\Evaluation;
use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\PlanningEvaluation\Tests\TestCase;
use Quochao56\Student\Models\Student;

uses(TestCase::class);

it('exports planning with subheaders and cell merges successfully', function () {
    // Create student and employee to satisfy relation loading
    $student = new Student;
    $student->name = 'Test Student';
    $student->gender = 'male';
    $student->dob = now();
    $student->save();

    $employee = new Employee;
    $employee->name = 'Test Employee';
    $employee->save();

    // Create Planning model
    $planning = new Planning;
    $planning->name = 'Test Planning';
    $planning->student_id = $student->id;
    $planning->employee_id = $employee->id;
    $planning->start_date = now();
    $planning->end_date = now()->addMonths(2);
    $planning->planning_details = [
        [
            'linh_vuc' => [
                ['content' => '**Kỹ năng tiền đề**'],
                ['content' => '- Chú ý'],
            ],
            'muc_tieu' => [['content' => '- Duy trì chú ý...']],
            'hoat_dong' => [['content' => '- Hoat dong 1']],
            'phuong_tien' => [['content' => '- Phuong tien 1']],
            'muc_tieu_du_phong' => [],
        ],
    ];
    $planning->save();

    // Subclass the action to call the protected method generateWordFile
    $action = new class('export_planning_word') extends ExportPlanningWordAction
    {
        public function callGenerateWordFile($record, $outputFile)
        {
            return $this->generateWordFile($record, $outputFile);
        }
    };

    $outputFile = 'test_output_'.time().'.docx';
    $filePath = $action->callGenerateWordFile($planning, $outputFile);

    expect(file_exists($filePath))->toBeTrue();

    // Inspect the generated docx file XML to verify merging tags
    $zip = new ZipArchive;
    if ($zip->open($filePath) === true) {
        $xml = $zip->getFromName('word/document.xml');

        // Check that it contains gridSpan (horizontal merge)
        expect($xml)->toContain('w:gridSpan');
        expect($xml)->toContain('w:val="4"');

        // Check that it contains vMerge (vertical merge)
        expect($xml)->toContain('w:vMerge');

        // Check that it contains shading (w:shd w:fill="F2F2F2")
        expect($xml)->toContain('w:shd');
        expect($xml)->toContain('w:fill="F2F2F2"');

        // Check that it contains the subcategory name
        expect($xml)->toContain('Chú ý');

        $zip->close();
    } else {
        $this->fail('Failed to open generated docx file');
    }

    // Clean up
    if (file_exists($filePath)) {
        unlink($filePath);
    }
});

it('replaces all tabs with spaces in planning_details when saving', function () {
    // Create student and employee to satisfy relation loading
    $student = new Student;
    $student->name = 'Test Student';
    $student->gender = 'male';
    $student->dob = now();
    $student->save();

    $employee = new Employee;
    $employee->name = 'Test Employee';
    $employee->save();

    // Create Planning model with tab characters in details
    $planning = new Planning;
    $planning->name = 'Test Planning';
    $planning->student_id = $student->id;
    $planning->employee_id = $employee->id;
    $planning->planning_details = [
        [
            'linh_vuc' => [
                ['content' => "**Kỹ năng tiền đề**\tSub"],
            ],
            'muc_tieu' => [
                ['content' => "-\tBắt chước"],
            ],
            'hoat_dong' => [],
            'phuong_tien' => [],
            'muc_tieu_du_phong' => [],
        ],
    ];
    $planning->save();

    // Refresh model and assert tabs are replaced with spaces
    $planning->refresh();

    expect($planning->planning_details[0]['linh_vuc'][0]['content'])->toBe('**Kỹ năng tiền đề** Sub');
    expect($planning->planning_details[0]['muc_tieu'][0]['content'])->toBe('- Bắt chước');
});

it('exports evaluation with subheaders and cell merges successfully', function () {
    // Create student and employee to satisfy relation loading
    $student = new Student;
    $student->name = 'Test Student';
    $student->gender = 'male';
    $student->dob = now();
    $student->save();

    $employee = new Employee;
    $employee->name = 'Test Employee';
    $employee->save();

    // Create Planning model
    $planning = new Planning;
    $planning->name = 'Test Planning';
    $planning->student_id = $student->id;
    $planning->employee_id = $employee->id;
    $planning->start_date = now();
    $planning->end_date = now()->addMonths(2);
    $planning->planning_details = [
        [
            'linh_vuc' => [
                ['content' => '**Kỹ năng tiền đề**'],
                ['content' => '- Chú ý'],
            ],
            'muc_tieu' => [['content' => '- Duy trì chú ý...']],
            'hoat_dong' => [['content' => '- Hoat dong 1']],
            'phuong_tien' => [['content' => '- Phuong tien 1']],
            'muc_tieu_du_phong' => [],
        ],
    ];
    $planning->save();

    // Create Evaluation from Planning
    $evaluation = Evaluation::upsertFromPlanning($planning);
    $evaluation->status = BaseStatusEnum::Published;

    // Set evaluation details assessment data
    $details = $evaluation->evaluation_details;
    $details[0]['muc_tieu'][0]['danh_gia'] = '+';
    $details[0]['muc_tieu'][0]['nhan_xet'] = 'Tốt';
    $evaluation->evaluation_details = $details;
    $evaluation->save();

    // Subclass the action to call the protected method generateWordFile
    $action = new class('export_evaluation_word') extends ExportEvaluationWordAction
    {
        public function callGenerateWordFile($record, $outputFile)
        {
            return $this->generateWordFile($record, $outputFile);
        }
    };

    $outputFile = 'test_eval_output_'.time().'.docx';
    $filePath = $action->callGenerateWordFile($evaluation, $outputFile);

    expect(file_exists($filePath))->toBeTrue();

    // Inspect the generated docx file XML to verify merging tags
    $zip = new ZipArchive;
    if ($zip->open($filePath) === true) {
        $xml = $zip->getFromName('word/document.xml');

        // Check that it contains gridSpan (horizontal merge) of 5
        expect($xml)->toContain('w:gridSpan');
        expect($xml)->toContain('w:val="5"');

        // Check that it contains vMerge (vertical merge)
        expect($xml)->toContain('w:vMerge');

        // Check that it contains shading (w:shd w:fill="F2F2F2")
        expect($xml)->toContain('w:shd');
        expect($xml)->toContain('w:fill="F2F2F2"');

        // Check that it contains the subcategory name
        expect($xml)->toContain('Chú ý');

        // Check that it contains the assessment comment
        expect($xml)->toContain('Tốt');

        $zip->close();
    } else {
        $this->fail('Failed to open generated docx file');
    }

    // Clean up
    if (file_exists($filePath)) {
        unlink($filePath);
    }
});
