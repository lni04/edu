-- 课程表添加学分和学期字段
ALTER TABLE course
  ADD COLUMN credit DECIMAL(3,1) NOT NULL DEFAULT 2.0 COMMENT '学分' AFTER capacity,
  ADD COLUMN semester VARCHAR(20) NOT NULL DEFAULT '2025Spring' COMMENT '开课学期' AFTER credit;

-- 学生表添加总学分和GPA字段
ALTER TABLE student
  ADD COLUMN total_credit DECIMAL(5,1) NOT NULL DEFAULT 0 COMMENT '已修学分' AFTER password,
  ADD COLUMN gpa DECIMAL(3,2) NOT NULL DEFAULT 0 COMMENT '总绩点' AFTER total_credit;

-- 成绩表添加学期字段和绩点
ALTER TABLE score
  ADD COLUMN semester VARCHAR(20) NOT NULL DEFAULT '2025Spring' COMMENT '学期' AFTER total_score,
  ADD COLUMN grade_point DECIMAL(3,2) GENERATED ALWAYS AS (
    CASE
      WHEN total_score >= 90 THEN 4.0
      WHEN total_score >= 85 THEN 3.7
      WHEN total_score >= 82 THEN 3.3
      WHEN total_score >= 78 THEN 3.0
      WHEN total_score >= 75 THEN 2.7
      WHEN total_score >= 72 THEN 2.3
      WHEN total_score >= 68 THEN 2.0
      WHEN total_score >= 64 THEN 1.5
      WHEN total_score >= 60 THEN 1.0
      ELSE 0
    END
  ) STORED COMMENT '绩点';

-- 更新示例课程数据
UPDATE course SET credit = 3.0, semester = '2025Spring' WHERE course_id = 'CS101';
UPDATE course SET credit = 4.0, semester = '2025Spring' WHERE course_id = 'CS102';

-- 创建触发器：成绩插入后更新学生GPA
DELIMITER //
CREATE TRIGGER trg_score_insert AFTER INSERT ON score
FOR EACH ROW
BEGIN
  UPDATE student s SET
    total_credit = (
      SELECT COALESCE(SUM(c.credit), 0)
      FROM score sc JOIN course c ON sc.course_id = c.course_id
      WHERE sc.student_id = NEW.student_id AND sc.total_score >= 60
    ),
    gpa = (
      SELECT COALESCE(ROUND(SUM(sc.grade_point * c.credit) / NULLIF(SUM(c.credit), 0), 2), 0)
      FROM score sc JOIN course c ON sc.course_id = c.course_id
      WHERE sc.student_id = NEW.student_id
    )
  WHERE s.student_id = NEW.student_id;
END//

CREATE TRIGGER trg_score_update AFTER UPDATE ON score
FOR EACH ROW
BEGIN
  UPDATE student s SET
    total_credit = (
      SELECT COALESCE(SUM(c.credit), 0)
      FROM score sc JOIN course c ON sc.course_id = c.course_id
      WHERE sc.student_id = NEW.student_id AND sc.total_score >= 60
    ),
    gpa = (
      SELECT COALESCE(ROUND(SUM(sc.grade_point * c.credit) / NULLIF(SUM(c.credit), 0), 2), 0)
      FROM score sc JOIN course c ON sc.course_id = c.course_id
      WHERE sc.student_id = NEW.student_id
    )
  WHERE s.student_id = NEW.student_id;
END//
DELIMITER ;

-- 手动更新现有学生的GPA
UPDATE student s SET
  total_credit = (
    SELECT COALESCE(SUM(c.credit), 0)
    FROM score sc JOIN course c ON sc.course_id = c.course_id
    WHERE sc.student_id = s.student_id AND sc.total_score >= 60
  ),
  gpa = (
    SELECT COALESCE(ROUND(SUM(sc.grade_point * c.credit) / NULLIF(SUM(c.credit), 0), 2), 0)
    FROM score sc JOIN course c ON sc.course_id = c.course_id
    WHERE sc.student_id = s.student_id
  );
