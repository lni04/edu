<?php
$auth = getAuthUser();
if (!$auth) error('未登录或token已过期', 401);

$input = json_decode(file_get_contents('php://input'), true);
$studentId = $input['student_id'] ?? '';
$courseId = $input['course_id'] ?? '';

if (empty($studentId) || empty($courseId)) {
    error('学号和课程号不能为空');
}

if ($auth['student_id'] !== $studentId) {
    error('只能为自己选课', 403);
}

$pdo = getDB();

$stmt = $pdo->prepare('SELECT 1 FROM enrollment WHERE student_id = ? AND course_id = ?');
$stmt->execute([$studentId, $courseId]);
if ($stmt->fetch()) {
    error('已选过该课程', 409);
}

try {
    $pdo->beginTransaction();

    // FOR UPDATE 加行级锁，同时检查课程是否存在
    $stmt = $pdo->prepare('SELECT capacity FROM course WHERE course_id = ? FOR UPDATE');
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();

    if (!$course) {
        $pdo->rollBack();
        error('课程不存在', 404);
    }

    // 锁定期间统计已选人数
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM enrollment WHERE course_id = ?');
    $stmt->execute([$courseId]);
    $enrolled = (int)$stmt->fetch()['cnt'];

    if ($enrolled >= (int)$course['capacity']) {
        $pdo->rollBack();
        error('课程容量已满', 400);
    }

    // 插入选课记录
    $stmt = $pdo->prepare('INSERT INTO enrollment (student_id, course_id) VALUES (?, ?)');
    $stmt->execute([$studentId, $courseId]);

    $pdo->commit();
    success(null, '选课成功');

} catch (PDOException $e) {
    $pdo->rollBack();
    error('选课失败：' . $e->getMessage(), 500);
}
