<?php

namespace Quochao56\Student;

class Student {

    public function generateStudentCode(): string
    {
        $latestStudent = Models\Student::latest()->first();
        $latestCode = $latestStudent ? (int) str_replace('HS', '', $latestStudent->student_code) : 0;
        return 'HS' . str_pad($latestCode + 1, 3, '0', STR_PAD_LEFT);
    }
}
