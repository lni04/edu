<?php
/**
 * =====================================================
 * 数据配置文件 - 直接修改这里的内容，前端会自动显示
 * =====================================================
 */

// ==================== 学生信息 ====================
// 修改下面的数据即可改变前端显示
$STUDENTS = [
    '2024001' => [
        'student_id' => '2024001',
        'name' => '张三',              // ← 修改姓名
        'class_name' => '软件工程2401', // ← 修改班级
        'phone' => '13800001111',       // ← 修改手机
        'password' => '123456',         // ← 修改密码
        'total_credit' => 12,           // ← 修改学分
        'gpa' => 3.65                   // ← 修改绩点
    ],
    '2024002' => [
        'student_id' => '2024002',
        'name' => '李四',
        'class_name' => '软件工程2401',
        'phone' => '13800002222',
        'password' => '123456',
        'total_credit' => 10,
        'gpa' => 3.42
    ],
    '2024003' => [
        'student_id' => '2024003',
        'name' => '王五',
        'class_name' => '计算机2402',
        'phone' => '13800003333',
        'password' => '123456',
        'total_credit' => 8,
        'gpa' => 3.21
    ],
];

// ==================== 课程信息 ====================
$COURSES = [
    'CS101' => [
        'course_id' => 'CS101',
        'course_name' => '数据库原理',   // ← 修改课程名
        'teacher' => '刘老师',           // ← 修改教师
        'credit' => 4,                   // ← 修改学分
        'capacity' => 60,                // ← 修改容量
        'semester' => '2025春季'         // ← 修改学期
    ],
    'CS102' => [
        'course_id' => 'CS102',
        'course_name' => '操作系统',
        'teacher' => '陈老师',
        'credit' => 4,
        'capacity' => 50,
        'semester' => '2025春季'
    ],
    'CS103' => [
        'course_id' => 'CS103',
        'course_name' => '计算机网络',
        'teacher' => '王老师',
        'credit' => 3,
        'capacity' => 45,
        'semester' => '2025春季'
    ],
    'CS104' => [
        'course_id' => 'CS104',
        'course_name' => '软件工程',
        'teacher' => '赵老师',
        'credit' => 3,
        'capacity' => 40,
        'semester' => '2024秋季'
    ],
];

// ==================== 成绩信息 ====================
$SCORES = [
    '2024001' => [
        ['course_id' => 'CS101', 'usual_score' => 85, 'final_score' => 90, 'semester' => '2025春季'],
        ['course_id' => 'CS104', 'usual_score' => 88, 'final_score' => 85, 'semester' => '2024秋季'],
    ],
    '2024002' => [
        ['course_id' => 'CS101', 'usual_score' => 78, 'final_score' => 82, 'semester' => '2025春季'],
    ],
    '2024003' => [
        ['course_id' => 'CS102', 'usual_score' => 80, 'final_score' => 75, 'semester' => '2025春季'],
    ],
];

// ==================== 选课记录 ====================
$ENROLLMENTS = [
    '2024001' => ['CS101', 'CS104'],
    '2024002' => ['CS101'],
    '2024003' => ['CS102'],
];

// ==================== 辅助函数（勿修改） ====================
function calcTotalScore($usual, $final) {
    return round($usual * 0.4 + $final * 0.6, 2);
}

function calcGradePoint($total) {
    if ($total >= 90) return 4.0;
    if ($total >= 85) return 3.7;
    if ($total >= 82) return 3.3;
    if ($total >= 78) return 3.0;
    if ($total >= 75) return 2.7;
    if ($total >= 72) return 2.3;
    if ($total >= 68) return 2.0;
    if ($total >= 64) return 1.5;
    if ($total >= 60) return 1.0;
    return 0;
}
