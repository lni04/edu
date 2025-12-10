# 教务系统 RESTful API

## 使用方法
1. 将 `edu_api` 文件夹复制到 `htdocs` 目录
2. 修改 `config.php` 中的数据库配置
3. 确保 Apache 启用了 `mod_rewrite` 模块

## API 端点

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | /login | 登录，返回 JWT |
| GET | /student/{id} | 查看个人信息 |
| GET | /grade/{id} | 查看成绩 |
| POST | /select | 选课 |

## 测试示例

\`\`\`bash
# 登录
curl -X POST http://localhost/edu_api/login \
  -H "Content-Type: application/json" \
  -d '{"student_id":"2024001","password":"123456"}'

# 查看个人信息
curl http://localhost/edu_api/student/2024001 \
  -H "Authorization: Bearer YOUR_TOKEN"

# 查看成绩
curl http://localhost/edu_api/grade/2024001 \
  -H "Authorization: Bearer YOUR_TOKEN"

# 选课
curl -X POST http://localhost/edu_api/select \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"student_id":"2024001","course_id":"CS102"}'
