<?php
$auth = getAuthUser();
if (!$auth) error('未登录或token已过期', 401);

$id = $GLOBALS['route_params'][0] ?? '';

if ($auth['student_id'] !== $id) {
    error('无权查看他人成绩', 403);
}

$pdo = getDB();
$sql = 'SELECT c.course_id, c.course_name, c.teacher,
               s.usual_score, s.final_score, s.total_score
        FROM score s
        JOIN course c ON s.course_id = c.course_id
        WHERE s.student_id = ?';
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$grades = $stmt->fetchAll();

success($grades);
