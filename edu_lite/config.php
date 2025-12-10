<?php
// SQLite 版本 - 无需 MySQL，无需权限
define('DB_FILE', __DIR__ . '/data/edu.db');
define('JWT_SECRET', '123456');
define('JWT_EXPIRE', 86400);

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dir = dirname(DB_FILE);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        
        $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // 首次运行自动建表
        initDatabase($pdo);
    }
    return $pdo;
}

function initDatabase(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS student (
        student_id   TEXT PRIMARY KEY,
        name         TEXT NOT NULL,
        class_name   TEXT NOT NULL,
        phone        TEXT,
        password     TEXT NOT NULL,
        total_credit REAL DEFAULT 0,
        gpa          REAL DEFAULT 0
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS course (
        course_id    TEXT PRIMARY KEY,
        course_name  TEXT NOT NULL,
        teacher      TEXT NOT NULL,
        capacity     INTEGER DEFAULT 0,
        credit       REAL DEFAULT 2.0,
        semester     TEXT DEFAULT '2025Spring'
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS score (
        student_id   TEXT NOT NULL,
        course_id    TEXT NOT NULL,
        usual_score  REAL,
        final_score  REAL,
        total_score  REAL,
        grade_point  REAL,
        PRIMARY KEY (student_id, course_id)
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS enrollment (
        student_id   TEXT NOT NULL,
        course_id    TEXT NOT NULL,
        enroll_time  TEXT DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (student_id, course_id)
    )");
    
    // 插入测试数据（如果为空）
    $count = $pdo->query("SELECT COUNT(*) FROM student")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO student VALUES 
            ('2024001','张三','软件2401','13800001111','123456',7.0,3.80),
            ('2024002','李四','软件2401','13800002222','123456',3.0,3.00),
            ('2024003','王五','软件2402','13800003333','123456',0,0)");
        
        $pdo->exec("INSERT INTO course VALUES 
            ('CS101','数据库原理','刘老师',60,3.0,'2025Spring'),
            ('CS102','操作系统','陈老师',50,4.0,'2025Spring'),
            ('CS103','计算机网络','王老师',45,3.5,'2024Fall')");
        
        $pdo->exec("INSERT INTO enrollment VALUES 
            ('2024001','CS101','2025-03-01 09:00:00'),
            ('2024002','CS101','2025-03-01 09:05:00'),
            ('2024001','CS102','2025-03-02 14:00:00')");
        
        $pdo->exec("INSERT INTO score VALUES 
            ('2024001','CS101',85.00,90.00,88.00,3.80),
            ('2024002','CS101',78.00,82.00,80.40,3.00),
            ('2024001','CS102',90.00,88.00,88.80,3.90)");
    }
}
