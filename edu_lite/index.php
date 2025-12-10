<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/data.php';

function success($data = null, $msg = 'ok') {
    echo json_encode(['code' => 0, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function error($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['code' => -1, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = preg_replace('#^/edu_lite#', '', $uri);
$method = $_SERVER['REQUEST_METHOD'];

// POST /login
if ($method === 'POST' && $uri === '/login') {
    global $STUDENTS;
    $input = json_decode(file_get_contents('php://input'), true);
    $id = trim($input['student_id'] ?? '');
    $pwd = $input['password'] ?? '';
    
    if (!$id || !$pwd) error('学号和密码不能为空');
    if (!isset($STUDENTS[$id])) error('学号不存在', 401);
    if ($STUDENTS[$id]['password'] !== $pwd) error('密码错误', 401);
    
    $user = $STUDENTS[$id];
    unset($user['password']);
    success(['user' => $user], '登录成功');
}

// GET /student/{id}/profile
if ($method === 'GET' && preg_match('#^/student/(\w+)/profile$#', $uri, $m)) {
    global $STUDENTS;
    $id = $m[1];
    if (!isset($STUDENTS[$id])) error('学生不存在', 404);
    $user = $STUDENTS[$id];
    unset($user['password']);
    success($user);
}

// GET /student/{id}
if ($method === 'GET' && preg_match('#^/student/(\w+)$#', $uri, $m)) {
    global $STUDENTS;
    $id = $m[1];
    if (!isset($STUDENTS[$id])) error('学生不存在', 404);
    $user = $STUDENTS[$id];
    unset($user['password']);
    success($user);
}

// GET /grade/{id}
if ($method === 'GET' && preg_match('#^/grade/(\w+)$#', $uri, $m)) {
    global $STUDENTS, $SCORES, $COURSES;
    $id = $m[1];
    if (!isset($STUDENTS[$id])) error('学生不存在', 404);
    
    $grades = [];
    if (isset($SCORES[$id])) {
        foreach ($SCORES[$id] as $score) {
            $course = $COURSES[$score['course_id']] ?? null;
            if ($course) {
                $total = calcTotalScore($score['usual_score'], $score['final_score']);
                $grades[] = [
                    'course_id' => $score['course_id'],
                    'course_name' => $course['course_name'],
                    'teacher' => $course['teacher'],
                    'credit' => $course['credit'],
                    'usual_score' => $score['usual_score'],
                    'final_score' => $score['final_score'],
                    'total_score' => $total,
                    'grade_point' => calcGradePoint($total),
                    'semester' => $score['semester']
                ];
            }
        }
    }
    success($grades);
}

// GET /student/{id}/grades
if ($method === 'GET' && preg_match('#^/student/(\w+)/grades$#', $uri, $m)) {
    global $STUDENTS, $SCORES, $COURSES;
    $id = $m[1];
    if (!isset($STUDENTS[$id])) error('学生不存在', 404);
    
    $grouped = [];
    if (isset($SCORES[$id])) {
        foreach ($SCORES[$id] as $score) {
            $course = $COURSES[$score['course_id']] ?? null;
            if ($course) {
                $total = calcTotalScore($score['usual_score'], $score['final_score']);
                $sem = $score['semester'];
                $grouped[$sem][] = [
                    'course_id' => $score['course_id'],
                    'course_name' => $course['course_name'],
                    'credit' => $course['credit'],
                    'usual_score' => $score['usual_score'],
                    'final_score' => $score['final_score'],
                    'total_score' => $total,
                    'grade_point' => calcGradePoint($total)
                ];
            }
        }
    }
    success($grouped);
}

// GET /student/{id}/gpa
if ($method === 'GET' && preg_match('#^/student/(\w+)/gpa$#', $uri, $m)) {
    global $STUDENTS, $SCORES, $COURSES;
    $id = $m[1];
    if (!isset($STUDENTS[$id])) error('学生不存在', 404);
    
    $student = $STUDENTS[$id];
    $semesters = [];
    
    if (isset($SCORES[$id])) {
        $semData = [];
        foreach ($SCORES[$id] as $score) {
            $course = $COURSES[$score['course_id']] ?? null;
            if ($course) {
                $total = calcTotalScore($score['usual_score'], $score['final_score']);
                $gp = calcGradePoint($total);
                $sem = $score['semester'];
                if (!isset($semData[$sem])) $semData[$sem] = ['credit' => 0, 'weighted' => 0];
                $semData[$sem]['credit'] += $course['credit'];
                $semData[$sem]['weighted'] += $gp * $course['credit'];
            }
        }
        foreach ($semData as $sem => $data) {
            $semesters[] = [
                'semester' => $sem,
                'credit' => $data['credit'],
                'gpa' => round($data['weighted'] / $data['credit'], 2)
            ];
        }
    }
    
    success([
        'total_credit' => $student['total_credit'],
        'gpa' => $student['gpa'],
        'semesters' => $semesters
    ]);
}

// GET /course 或 /courses
if ($method === 'GET' && ($uri === '/course' || $uri === '/courses')) {
    global $COURSES, $ENROLLMENTS;
    $list = [];
    foreach ($COURSES as $course) {
        $enrolled = 0;
        foreach ($ENROLLMENTS as $stuEnroll) {
            if (in_array($course['course_id'], $stuEnroll)) $enrolled++;
        }
        $list[] = [
            'course_id' => $course['course_id'],
            'course_name' => $course['course_name'],
            'teacher' => $course['teacher'],
            'credit' => $course['credit'],
            'capacity' => $course['capacity'],
            'semester' => $course['semester'],
            'enrolled' => $enrolled,
            'remaining' => $course['capacity'] - $enrolled
        ];
    }
    success($list);
}

// POST /select - 选课（无需验证）
if ($method === 'POST' && $uri === '/select') {
    global $STUDENTS, $COURSES, $ENROLLMENTS;
    $input = json_decode(file_get_contents('php://input'), true);
    $studentId = trim($input['student_id'] ?? '');
    $courseId = trim($input['course_id'] ?? '');
    
    if (!$studentId || !$courseId) error('学号和课程号不能为空');
    if (!isset($STUDENTS[$studentId])) error('学生不存在', 404);
    if (!isset($COURSES[$courseId])) error('课程不存在', 404);
    
    if (isset($ENROLLMENTS[$studentId]) && in_array($courseId, $ENROLLMENTS[$studentId])) {
        error('已选过该课程', 409);
    }
    
    $enrolled = 0;
    foreach ($ENROLLMENTS as $stuEnroll) {
        if (in_array($courseId, $stuEnroll)) $enrolled++;
    }
    if ($enrolled >= $COURSES[$courseId]['capacity']) {
        error('课程容量已满', 400);
    }
    
    // 注意：这里选课成功只是内存操作，刷新后会重置
    // 如需持久化，请使用数据库版本
    success(null, '选课成功！（注意：数据未持久化，刷新后重置）');
}

error('接口不存在: ' . $uri, 404);
