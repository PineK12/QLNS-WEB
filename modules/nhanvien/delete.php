<?php
session_start();
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $db = new Database();
    $id = $_GET['id'];

    // Xóa nhân viên
    $db->query("DELETE FROM nhanvien WHERE id = :id");
    $db->bind(':id', $id);

    if ($db->execute()) {
        $_SESSION['success'] = "Đã xóa nhân viên thành công!";
    } else {
        // Nếu lỗi do ràng buộc khóa ngoại (ví dụ nhân viên đã có chấm công)
        $_SESSION['error'] = "Không thể xóa nhân viên này vì đã có dữ liệu liên quan!";
    }
}

// Quay lại trang danh sách
header("Location: ../../index.php?page=nhanvien_list");
?>