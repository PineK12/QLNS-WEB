<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();

    // Lấy dữ liệu từ Form
    $id = $_POST['id'];
    $ho = $_POST['ho'];
    $ten = $_POST['ten'];
    $sdt = $_POST['sdt'];
    $diachi = $_POST['diachi'];
    $ngaysinh = $_POST['ngaysinh'];
    $id_role = $_POST['id_role'];
    $id_trinhdo = $_POST['id_trinhdo'];
    $luongcoban = $_POST['luongcoban'];
    $ngayvaolam = $_POST['ngayvaolam'];

    // Câu lệnh Update
    $sql = "UPDATE nhanvien SET 
            ho=:ho, ten=:ten, sdt=:sdt, diachi=:diachi, ngaysinh=:ngaysinh, 
            id_role=:id_role, id_trinhdo=:id_trinhdo, luongcoban=:luongcoban, ngayvaolam=:ngayvaolam 
            WHERE id=:id";

    $db->query($sql);
    $db->bind(':id', $id);
    $db->bind(':ho', $ho);
    $db->bind(':ten', $ten);
    $db->bind(':sdt', $sdt);
    $db->bind(':diachi', $diachi);
    $db->bind(':ngaysinh', $ngaysinh);
    $db->bind(':id_role', $id_role);
    $db->bind(':id_trinhdo', $id_trinhdo);
    $db->bind(':luongcoban', $luongcoban);
    $db->bind(':ngayvaolam', $ngayvaolam);

    if ($db->execute()) {
        $_SESSION['success'] = "Cập nhật nhân viên thành công!";
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra!";
    }

    // Quay lại trang danh sách
    header("Location: ../../index.php?page=nhanvien_list");
}
?>