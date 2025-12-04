<?php
// --- THÊM 2 DÒNG NÀY ĐỂ HIỆN LỖI (DEBUG) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// -------------------------------------------

// 1. Nhúng file
require_once './config/database.php';

// 2. Khởi tạo
$db = new Database();

// 3. Query
// Lưu ý: Trong ảnh bạn có chữ "sql:". Nếu đó là do bạn tự gõ vào thì XÓA đi.
$db->query("SELECT * FROM nhanvien");

// 4. Lấy kết quả
$danhSachNhanVien = $db->resultSet();

// 5. Hiện kết quả
echo "<h1>Kết nối OK!</h1>";
echo "<pre>";
print_r($danhSachNhanVien);
echo "</pre>";
?>