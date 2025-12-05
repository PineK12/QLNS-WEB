<?php
// File API lấy chi tiết chấm công
// Đặt tại: pages/chamcong_detail_api.php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$db = new Database();

// Lấy ID từ request
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID không hợp lệ'
    ]);
    exit;
}

try {
    $db->query("SELECT 
                    cc.id,
                    cc.id_nv,
                    CONCAT(nv.ho, ' ', nv.ten) as ho_ten,
                    nv.sdt,
                    nv.diachi,
                    nv.ngaysinh,
                    r.ten as chuc_vu,
                    td.loai as trinh_do,
                    cc.ngay,
                    cc.gio_vao,
                    cc.gio_ra,
                    cc.trangthai,
                    CASE 
                        WHEN cc.gio_vao IS NOT NULL AND cc.gio_ra IS NOT NULL 
                        THEN TIMESTAMPDIFF(MINUTE, cc.gio_vao, cc.gio_ra)
                        ELSE NULL
                    END as so_phut_lam
                FROM chamcong cc
                INNER JOIN nhanvien nv ON cc.id_nv = nv.id
                LEFT JOIN role r ON nv.id_role = r.id
                LEFT JOIN trinhdo td ON nv.id_trinhdo = td.id
                WHERE cc.id = :id");
    
    $db->bind(':id', $id);
    $result = $db->single();
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'data' => $result
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy bản ghi'
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