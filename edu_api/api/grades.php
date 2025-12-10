<?php
$auth = getAuthUser();
if (!$auth) error('未登录或token已过期', 401);

$id = $GLOBALS['route_params'][0] ?? '';

if ($auth['student_id'] !== $id) {
    error('无权查看他人成绩', 403);
}

$pdo = getDB();
$sql = '
    SELECT c.course_id, c.course_name, c.credit,
           s.usual_score, s.final_score, s.total_score, 
           s.grade_point, s.semester
    FROM score s
    JOIN course c ON s.course_id = c.course_id
    WHERE s.student_id = ?
    ORDER BY s.semester DESC, c.course_id
';
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$rows = $stmt->fetchAll();

// 按学期分组
$grouped = [];
foreach ($rows as $row) {
    $sem = $row['semester'];
    if (!isset($grouped[$sem])) {
        $grouped[$sem] = [];
    }
    $grouped[$sem][] = [
        'course_id'   => $row['course_id'],
        'course_name' => $row['course_name'],
        'credit'      => (float) $row['credit'],
        'usual_score' => (float) $row['usual_score'],
        'final_score' => (float) $row['final_score'],
        'total_score' => (float) $row['total_score'],
        'grade_point' => (float) $row['grade_point']
    ];
}

success($grouped);
