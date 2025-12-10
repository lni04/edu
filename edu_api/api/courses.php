<?php
$auth = getAuthUser();
if (!$auth) error('未登录或token已过期', 401);

$pdo = getDB();

// 可选学期筛选
$semester = $_GET['semester'] ?? null;

$sql = '
    SELECT c.course_id, c.course_name, c.teacher, c.capacity, c.credit, c.semester,
           COUNT(e.student_id) as enrolled,
           (c.capacity - COUNT(e.student_id)) as remaining
    FROM course c
    LEFT JOIN enrollment e ON c.course_id = e.course_id
';

$params = [];
if ($semester) {
    $sql .= ' WHERE c.semester = ?';
    $params[] = $semester;
}

$sql .= ' GROUP BY c.course_id ORDER BY c.semester DESC, c.course_id';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

$result = [];
foreach ($courses as $c) {
    $result[] = [
        'course_id'   => $c['course_id'],
        'course_name' => $c['course_name'],
        'teacher'     => $c['teacher'],
        'capacity'    => (int) $c['capacity'],
        'credit'      => (float) $c['credit'],
        'semester'    => $c['semester'],
        'enrolled'    => (int) $c['enrolled'],
        'remaining'   => (int) $c['remaining']
    ];
}

success($result);
