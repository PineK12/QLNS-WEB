<?php
session_start();
// Lưu ý đường dẫn gọi file config từ thư mục modules/nhanvien/
require_once '../../config/database.php';
require_once '../../class/NhanVien.php';

// Kiểm tra nếu người dùng bấm nút Submit (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Lấy dữ liệu từ Form
    $data = [
        'ho' => $_POST['ho'],
        'ten' => $_POST['ten'],
        'diachi' => $_POST['diachi'],
        'sdt' => $_POST['sdt'],
        'ngaysinh' => $_POST['ngaysinh'],
        'id_trinhdo' => $_POST['id_trinhdo'],
        'id_role' => $_POST['id_role'],
        'luongcoban' => $_POST['luongcoban'],
        'ngayvaolam' => $_POST['ngayvaolam']
    ];

    // Gọi Model để thêm
    $nvModel = new NhanVien();
    if ($nvModel->add($data)) {
        // Thành công -> Tạo thông báo và quay về trang danh sách
        $_SESSION['success'] = "Thêm mới nhân viên thành công!";
        header("Location: ../../index.php?page=nhanvien_list");
        exit();
    } else {
        // Thất bại -> Báo lỗi và quay lại trang thêm
        $_SESSION['error'] = "Lỗi: Không thể thêm nhân viên!";
        header("Location: ../../index.php?page=nhanvien_add");
        exit();
    }
} else {
    // Nếu truy cập trực tiếp file này mà không post dữ liệu -> Đẩy về trang chủ
    header("Location: ../../index.php");
}
?>