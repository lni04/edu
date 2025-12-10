<?php
$input = json_decode(file_get_contents('php://input'), true);
$studentId = $input['student_id'] ?? '';
$password = $input['password'] ?? '';

if (empty($studentId) || empty($password)) {
    error('学号和密码不能为空');
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT student_id, name, class_name FROM student WHERE student_id = ? AND password = ?');
$stmt->execute([$studentId, $password]);
$user = $stmt->fetch();

if (!$user) {
    error('学号或密码错误', 401);
}

$token = createJWT([
    'student_id' => $user['student_id'],
    'name' => $user['name']
]);

success([
    'token' => $token,
    'user' => $user
], '登录成功');
