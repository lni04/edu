<?php
$auth = getAuthUser();
if (!$auth) error('未登录或token已过期', 401);

$id = $GLOBALS['route_params'][0] ?? '';

if ($auth['student_id'] !== $id) {
    error('无权查看他人绩点', 403);
}

$pdo = getDB();

// 获取总绩点和总学分
$stmt = $pdo->prepare('SELECT total_credit, gpa FROM student WHERE student_id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    error('学生不存在', 404);
}

// 获取各学期绩点明细
$sql = '
    SELECT s.semester,
           SUM(c.credit) as semester_credit,
           ROUND(SUM(s.grade_point * c.credit) / SUM(c.credit), 2) as semester_gpa
    FROM score s
    JOIN course c ON s.course_id = c.course_id
    WHERE s.student_id = ?
    GROUP BY s.semester
    ORDER BY s.semester DESC
';
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$semesters = $stmt->fetchAll();

$semesterDetails = [];
foreach ($semesters as $sem) {
    $semesterDetails[] = [
        'semester'        => $sem['semester'],
        'semester_credit' => (float) $sem['semester_credit'],
        'semester_gpa'    => (float) $sem['semester_gpa']
    ];
}

success([
    'total_credit'     => (float) $student['total_credit'],
    'total_gpa'        => number_format((float) $student['gpa'], 2),
    'semester_details' => $semesterDetails
]);
