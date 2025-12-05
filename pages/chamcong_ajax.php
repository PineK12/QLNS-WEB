<?php
// File xử lý AJAX cho chấm công
// Đặt file này ở: pages/chamcong_ajax.php

header('Content-Type: application/json');

// Include Database class
require_once __DIR__ . '/../config/database.php';

// Khởi tạo Database object
$db = new Database();

// Lấy ngày hiện tại
$today = date('Y-m-d');

// Kiểm tra request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Lấy dữ liệu từ POST
$nv_id = isset($_POST['nhanvien_id']) ? intval($_POST['nhanvien_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$time_now = date('H:i:s');

// Validate input
if ($nv_id <= 0 || empty($action)) {
    echo json_encode([
        'success' => false,
        'message' => 'Dữ liệu không hợp lệ'
    ]);
    exit;
}

try {
    if ($action == 'checkin') {
        // Kiểm tra xem nhân viên đã check in hôm nay chưa
        $db->query("SELECT COUNT(*) as count FROM chamcong WHERE id_nv = :id AND ngay = :ngay");
        $db->bind(':id', $nv_id);
        $db->bind(':ngay', $today);
        $result = $db->single();
        
        if ($result->count == 0) {
            // Thêm bản ghi mới
            $db->query("INSERT INTO chamcong (id_nv, ngay, gio_vao, trangthai) VALUES (:id, :ngay, :gio, 'DiLam')");
            $db->bind(':id', $nv_id);
            $db->bind(':ngay', $today);
            $db->bind(':gio', $time_now);
            $db->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Check-in thành công lúc ' . date('H:i', strtotime($time_now)),
                'time' => date('H:i', strtotime($time_now)),
                'action' => 'checkin'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Nhân viên đã check-in hôm nay!'
            ]);
        }
        
    } elseif ($action == 'checkout') {
        // Kiểm tra xem đã check-in chưa
        $db->query("SELECT COUNT(*) as count FROM chamcong WHERE id_nv = :id AND ngay = :ngay AND gio_vao IS NOT NULL");
        $db->bind(':id', $nv_id);
        $db->bind(':ngay', $today);
        $result = $db->single();
        
        if ($result->count > 0) {
            // Cập nhật giờ ra
            $db->query("UPDATE chamcong SET gio_ra = :gio WHERE id_nv = :id AND ngay = :ngay AND gio_ra IS NULL");
            $db->bind(':gio', $time_now);
            $db->bind(':id', $nv_id);
            $db->bind(':ngay', $today);
            $db->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Check-out thành công lúc ' . date('H:i', strtotime($time_now)),
                'time' => date('H:i', strtotime($time_now)),
                'action' => 'checkout'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Nhân viên chưa check-in hôm nay!'
            ]);
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Hành động không hợp lệ'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}

exit;
?>