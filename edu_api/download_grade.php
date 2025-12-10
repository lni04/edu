<?php
/**
 * 成绩导出 Excel
 * 访问示例: download_grade.php?student_id=2024001
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$studentId = $_GET['student_id'] ?? '';

if (empty($studentId)) {
    http_response_code(400);
    die('缺少 student_id 参数');
}

$pdo = getDB();

// 查询学生信息
$stmt = $pdo->prepare('SELECT name, class_name FROM student WHERE student_id = ?');
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) {
    http_response_code(404);
    die('学生不存在');
}

// 查询成绩
$sql = 'SELECT c.course_id, c.course_name, c.teacher,
               s.usual_score, s.final_score, s.total_score
        FROM score s
        JOIN course c ON s.course_id = c.course_id
        WHERE s.student_id = ?
        ORDER BY c.course_id';
$stmt = $pdo->prepare($sql);
$stmt->execute([$studentId]);
$grades = $stmt->fetchAll();

// 创建 Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('成绩单');

// 标题行
$sheet->setCellValue('A1', "学生成绩单");
$sheet->mergeCells('A1:F1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// 学生信息
$sheet->setCellValue('A2', "学号: {$studentId}");
$sheet->setCellValue('C2', "姓名: {$student['name']}");
$sheet->setCellValue('E2', "班级: {$student['class_name']}");

// 表头
$headers = ['课程号', '课程名', '任课教师', '平时成绩', '期末成绩', '总评成绩'];
$columns = ['A', 'B', 'C', 'D', 'E', 'F'];

foreach ($headers as $i => $header) {
    $sheet->setCellValue($columns[$i] . '4', $header);
}

// 表头样式
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$sheet->getStyle('A4:F4')->applyFromArray($headerStyle);

// 填充数据
$row = 5;
foreach ($grades as $grade) {
    $sheet->setCellValue("A{$row}", $grade['course_id']);
    $sheet->setCellValue("B{$row}", $grade['course_name']);
    $sheet->setCellValue("C{$row}", $grade['teacher']);
    $sheet->setCellValue("D{$row}", $grade['usual_score']);
    $sheet->setCellValue("E{$row}", $grade['final_score']);
    $sheet->setCellValue("F{$row}", $grade['total_score']);
    $row++;
}

// 数据区域样式
$dataStyle = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
if ($row > 5) {
    $sheet->getStyle("A5:F" . ($row - 1))->applyFromArray($dataStyle);
}

// 设置列宽
$sheet->getColumnDimension('A')->setWidth(12);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(12);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(12);
$sheet->getColumnDimension('F')->setWidth(12);

// 输出下载
$filename = "成绩单_{$studentId}_{$student['name']}.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
