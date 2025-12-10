-- 创建数据库
CREATE DATABASE IF NOT EXISTS edu_system
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE edu_system;

-- 学生表
DROP TABLE IF EXISTS enrollment;
DROP TABLE IF EXISTS score;
DROP TABLE IF EXISTS course;
DROP TABLE IF EXISTS student;

CREATE TABLE student (
  student_id   VARCHAR(20)  NOT NULL PRIMARY KEY COMMENT '学号',
  name         VARCHAR(50)  NOT NULL COMMENT '姓名',
  class_name   VARCHAR(50)  NOT NULL COMMENT '班级',
  phone        VARCHAR(20)  COMMENT '手机',
  password     VARCHAR(255) NOT NULL COMMENT '密码',
  total_credit DECIMAL(5,1) NOT NULL DEFAULT 0 COMMENT '已修学分',
  gpa          DECIMAL(3,2) NOT NULL DEFAULT 0.00 COMMENT '总绩点'
) ENGINE=InnoDB COMMENT='学生表';

-- 课程表
CREATE TABLE course (
  course_id    VARCHAR(20)  NOT NULL PRIMARY KEY COMMENT '课程号',
  course_name  VARCHAR(100) NOT NULL COMMENT '课程名',
  teacher      VARCHAR(50)  NOT NULL COMMENT '教师',
  capacity     INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '容量',
  credit       DECIMAL(3,1) NOT NULL DEFAULT 2.0 COMMENT '学分',
  semester     VARCHAR(20)  NOT NULL DEFAULT '2025Spring' COMMENT '开课学期'
) ENGINE=InnoDB COMMENT='课程表';

-- 成绩表
CREATE TABLE score (
  student_id   VARCHAR(20) NOT NULL COMMENT '学号',
  course_id    VARCHAR(20) NOT NULL COMMENT '课程号',
  usual_score  DECIMAL(5,2) DEFAULT NULL COMMENT '平时成绩',
  final_score  DECIMAL(5,2) DEFAULT NULL COMMENT '期末成绩',
  total_score  DECIMAL(5,2) GENERATED ALWAYS AS (usual_score * 0.4 + final_score * 0.6) STORED COMMENT '总评',
  PRIMARY KEY (student_id, course_id),
  CONSTRAINT fk_score_student FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_score_course  FOREIGN KEY (course_id)  REFERENCES course(course_id)  ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='成绩表';

-- 选课结果表
CREATE TABLE enrollment (
  student_id   VARCHAR(20) NOT NULL COMMENT '学号',
  course_id    VARCHAR(20) NOT NULL COMMENT '课程号',
  enroll_time  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '选课时间',
  PRIMARY KEY (student_id, course_id),
  CONSTRAINT fk_enroll_student FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_enroll_course  FOREIGN KEY (course_id)  REFERENCES course(course_id)  ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='选课结果表';

-- 触发器：成绩变动时更新学生的学分和GPA
DELIMITER $$

DROP TRIGGER IF EXISTS trg_score_insert$$
CREATE TRIGGER trg_score_insert AFTER INSERT ON score
FOR EACH ROW
BEGIN
  CALL update_student_gpa(NEW.student_id);
END$$

DROP TRIGGER IF EXISTS trg_score_update$$
CREATE TRIGGER trg_score_update AFTER UPDATE ON score
FOR EACH ROW
BEGIN
  CALL update_student_gpa(NEW.student_id);
END$$

DROP TRIGGER IF EXISTS trg_score_delete$$
CREATE TRIGGER trg_score_delete AFTER DELETE ON score
FOR EACH ROW
BEGIN
  CALL update_student_gpa(OLD.student_id);
END$$

-- 存储过程：计算并更新学生GPA
DROP PROCEDURE IF EXISTS update_student_gpa$$
CREATE PROCEDURE update_student_gpa(IN sid VARCHAR(20))
BEGIN
  DECLARE v_total_credit DECIMAL(5,1) DEFAULT 0;
  DECLARE v_gpa DECIMAL(3,2) DEFAULT 0.00;
  
  SELECT 
    COALESCE(SUM(c.credit), 0),
    COALESCE(ROUND(SUM(
      CASE 
        WHEN s.total_score >= 90 THEN 4.0 * c.credit
        WHEN s.total_score >= 85 THEN 3.7 * c.credit
        WHEN s.total_score >= 82 THEN 3.3 * c.credit
        WHEN s.total_score >= 78 THEN 3.0 * c.credit
        WHEN s.total_score >= 75 THEN 2.7 * c.credit
        WHEN s.total_score >= 72 THEN 2.3 * c.credit
        WHEN s.total_score >= 68 THEN 2.0 * c.credit
        WHEN s.total_score >= 64 THEN 1.5 * c.credit
        WHEN s.total_score >= 60 THEN 1.0 * c.credit
        ELSE 0
      END
    ) / NULLIF(SUM(c.credit), 0), 2), 0)
  INTO v_total_credit, v_gpa
  FROM score s
  JOIN course c ON s.course_id = c.course_id
  WHERE s.student_id = sid AND s.total_score IS NOT NULL;
  
  UPDATE student SET total_credit = v_total_credit, gpa = v_gpa WHERE student_id = sid;
END$$

DELIMITER ;

-- 示范数据：学生
INSERT INTO student (student_id, name, class_name, phone, password) VALUES
  ('2024001', '张三', '软件2401', '13800001111', '123456'),
  ('2024002', '李四', '软件2401', '13800002222', '123456'),
  ('2024003', '王五', '软件2402', '13800003333', '123456');

-- 示范数据：课程
INSERT INTO course (course_id, course_name, teacher, capacity, credit, semester) VALUES
  ('CS101', '数据库原理', '刘老师', 60, 3.0, '2025Spring'),
  ('CS102', '操作系统', '陈老师', 50, 4.0, '2025Spring'),
  ('CS103', '计算机网络', '王老师', 45, 3.5, '2025Spring'),
  ('CS201', '数据结构', '张老师', 55, 4.0, '2024Fall'),
  ('CS202', '算法设计', '李老师', 40, 3.0, '2024Fall');

-- 示范数据：选课
INSERT INTO enrollment (student_id, course_id, enroll_time) VALUES
  ('2024001', 'CS101', '2025-03-01 09:00:00'),
  ('2024002', 'CS101', '2025-03-01 09:05:00'),
  ('2024001', 'CS102', '2025-03-02 14:00:00');

-- 示范数据：成绩
INSERT INTO score (student_id, course_id, usual_score, final_score) VALUES
  ('2024001', 'CS101', 85.00, 90.00),
  ('2024002', 'CS101', 78.00, 82.00),
  ('2024001', 'CS201', 90.00, 88.00);
