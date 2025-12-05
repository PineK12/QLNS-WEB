<?php
// File API xóa bản ghi chấm công
// Đặt tại: pages/chamcong_delete_api.php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$db = new Database();

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Lấy ID từ POST
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID không hợp lệ'
    ]);
    exit;
}

try {
    // Kiểm tra bản ghi tồn tại
    $db->query("SELECT COUNT(*) as count FROM chamcong WHERE id = :id");
    $db->bind(':id', $id);
    $result = $db->single();
    
    if ($result->count == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Bản ghi không tồn tại'
        ]);
        exit;
    }
    
    // Xóa bản ghi
    $db->query("DELETE FROM chamcong WHERE id = :id");
    $db->bind(':id', $id);
    $db->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Xóa bản ghi thành công'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}

exit;
?>