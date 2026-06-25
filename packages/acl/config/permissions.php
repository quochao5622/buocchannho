<?php

return [
    'categories' => [
        'acl::rbac.categories.academic_evaluation' => [
            'manage_plans_evaluations' => 'acl::rbac.permissions.manage_plans_evaluations',
            'view_global_tracker' => 'acl::rbac.permissions.view_global_tracker',
            'view_progress_reports' => 'acl::rbac.permissions.view_progress_reports',
        ],
        'acl::rbac.categories.student_staff' => [
            'assign_students' => 'acl::rbac.permissions.assign_students',
        ],
        'acl::rbac.categories.equipment' => [
            'check_equipment' => 'acl::rbac.permissions.check_equipment',
        ],
    ]
];
