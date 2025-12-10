<?php
$pdo = getDB();
$sql = 'SELECT c.course_id, c.course_name, c.teacher, c.capacity,
               COALESCE(COUNT(e.student_id), 0) AS enrolled
        FROM course c
        LEFT JOIN enrollment e ON c.course_id = e.course_id
        GROUP BY c.course_id';
$stmt = $pdo->query($sql);
$courses = $stmt->fetchAll();

// 计算剩余容量
foreach ($courses as &$course) {
    $course['enrolled'] = (int)$course['enrolled'];
    $course['available'] = (int)$course['capacity'] - $course['enrolled'];
}

success($courses);
