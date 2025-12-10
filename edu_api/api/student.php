<?php
$auth = getAuthUser();
if (!$auth) error('未登录或token已过期', 401);

$id = $GLOBALS['route_params'][0] ?? '';

if ($auth['student_id'] !== $id) {
    error('无权查看他人信息', 403);
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT student_id, name, class_name, phone FROM student WHERE student_id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    error('学生不存在', 404);
}

success($student);
