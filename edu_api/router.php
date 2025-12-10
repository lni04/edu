<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = preg_replace('#^/edu_api#', '', $uri);
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'POST /login'                 => 'api/login.php',
    'GET /student/(\w+)/profile'  => 'api/profile.php',
    'GET /student/(\w+)/grades'   => 'api/grades.php',
    'GET /student/(\w+)/gpa'      => 'api/gpa.php',
    'GET /student/(\w+)'          => 'api/student.php',
    'GET /grade/(\w+)'            => 'api/grade.php',
    'GET /courses'                => 'api/courses.php',
    'GET /course'                 => 'api/course.php',
    'POST /select'                => 'api/select.php',
];

foreach ($routes as $pattern => $file) {
    [$routeMethod, $routePath] = explode(' ', $pattern, 2);
    if ($method !== $routeMethod) continue;
    
    $regex = '#^' . $routePath . '$#';
    if (preg_match($regex, $uri, $matches)) {
        array_shift($matches);
        $GLOBALS['route_params'] = $matches;
        require_once __DIR__ . '/' . $file;
        exit;
    }
}

error('Not Found', 404);
