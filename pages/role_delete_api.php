<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID vai trò không hợp lệ'
        ]);
        exit;
    }

    try {
        // Kiểm tra xem vai trò có nhân viên không
        $db->query("SELECT COUNT(*) as total FROM nhanvien WHERE id_role = :id");
        $db->bind(':id', $id);
        $result = $db->single();

        if ($result->total > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể xóa vai trò này vì còn ' . $result->total . ' nhân viên đang sử dụng!'
            ]);
            exit;
        }

        // Xóa vai trò
        $db->query("DELETE FROM role WHERE id = :id");
        $db->bind(':id', $id);
        $db->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Xóa vai trò thành công!'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không hợp lệ'
    ]);
}
?>