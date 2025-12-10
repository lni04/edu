-- 不需要 CREATE DATABASE 权限，先在 phpMyAdmin 手动创建 edu_system 数据库
-- 然后选中该数据库，执行以下语句

-- 学生表
CREATE TABLE IF NOT EXISTS student (
  student_id   VARCHAR(20)  NOT NULL PRIMARY KEY,
  name         VARCHAR(50)  NOT NULL,
  class_name   VARCHAR(50)  NOT NULL,
  phone        VARCHAR(20)  DEFAULT NULL,
  password     VARCHAR(255) NOT NULL,
  total_credit DECIMAL(5,1) DEFAULT 0,
  gpa          DECIMAL(3,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 课程表
CREATE TABLE IF NOT EXISTS course (
  course_id    VARCHAR(20)  NOT NULL PRIMARY KEY,
  course_name  VARCHAR(100) NOT NULL,
  teacher      VARCHAR(50)  NOT NULL,
  capacity     INT UNSIGNED NOT NULL DEFAULT 0,
  credit       DECIMAL(3,1) NOT NULL DEFAULT 2.0,
  semester     VARCHAR(20)  DEFAULT '2025Spring'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 成绩表
CREATE TABLE IF NOT EXISTS score (
  student_id   VARCHAR(20) NOT NULL,
  course_id    VARCHAR(20) NOT NULL,
  usual_score  DECIMAL(5,2) DEFAULT NULL,
  final_score  DECIMAL(5,2) DEFAULT NULL,
  total_score  DECIMAL(5,2) DEFAULT NULL,
  grade_point  DECIMAL(3,2) DEFAULT NULL,
  PRIMARY KEY (student_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 选课表
CREATE TABLE IF NOT EXISTS enrollment (
  student_id   VARCHAR(20) NOT NULL,
  course_id    VARCHAR(20) NOT NULL,
  enroll_time  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (student_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入测试数据
INSERT INTO student (student_id, name, class_name, phone, password) VALUES
('2024001', '张三', '软件2401', '13800001111', '123456'),
('2024002', '李四', '软件2401', '13800002222', '123456'),
('2024003', '王五', '软件2402', '13800003333', '123456');

INSERT INTO course (course_id, course_name, teacher, capacity, credit, semester) VALUES
('CS101', '数据库原理', '刘老师', 60, 3.0, '2025Spring'),
('CS102', '操作系统', '陈老师', 50, 4.0, '2025Spring'),
('CS103', '计算机网络', '王老师', 45, 3.5, '2024Fall');

INSERT INTO enrollment (student_id, course_id) VALUES
('2024001', 'CS101'),
('2024002', 'CS101'),
('2024001', 'CS102');

INSERT INTO score (student_id, course_id, usual_score, final_score, total_score, grade_point) VALUES
('2024001', 'CS101', 85.00, 90.00, 88.00, 3.80),
('2024002', 'CS101', 78.00, 82.00, 80.40, 3.00);
