<?php
// Include file Database class
require_once __DIR__ . '/../config/database.php';

// Khởi tạo Database object
$db = new Database();

// Lấy ngày hiện tại
$today = date('Y-m-d');

// --- LẤY DANH SÁCH NHÂN VIÊN & TRẠNG THÁI CHẤM CÔNG HÔM NAY ---
try {
    $db->query("SELECT 
                    nv.id, 
                    CONCAT(nv.ho, ' ', nv.ten) as ho_ten,
                    nv.sdt,
                    nv.diachi,
                    r.ten as chuc_vu,
                    td.loai as trinh_do,
                    cc.gio_vao, 
                    cc.gio_ra,
                    cc.trangthai
                FROM nhanvien nv 
                LEFT JOIN chamcong cc ON nv.id = cc.id_nv AND cc.ngay = :today
                LEFT JOIN role r ON nv.id_role = r.id
                LEFT JOIN trinhdo td ON nv.id_trinhdo = td.id
                ORDER BY nv.id ASC");
    $db->bind(':today', $today);
    $list_nv = $db->resultSet();
} catch (Exception $e) {
    $list_nv = [];
    $error_message = "Lỗi truy vấn database: " . $e->getMessage();
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Chấm Công Hôm Nay</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Chấm công</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="alert alert-info">
                <i class="fas fa-calendar-alt"></i> Hôm nay: <strong><?php echo date('d/m/Y'); ?></strong>
            </div>

            <!-- Alert động cho thông báo -->
            <div id="notification-area"></div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách nhân viên</h3>
                </div>

                <div class="card-body p-0">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th style="width: 20%">Họ Tên</th>
                                <th style="width: 15%">Vai trò</th>
                                <th style="width: 12%">Giờ vào</th>
                                <th style="width: 12%">Giờ ra</th>
                                <th style="width: 13%">Trạng thái</th>
                                <th style="width: 23%" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($list_nv) > 0): ?>
                                <?php foreach ($list_nv as $nv): ?>
                                    <tr id="row-<?php echo $nv->id; ?>">
                                        <td><?php echo htmlspecialchars($nv->id); ?></td>
                                        <td>
                                            <a><?php echo htmlspecialchars($nv->ho_ten); ?></a>
                                            <br />
                                            <small class="text-muted">
                                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($nv->sdt ?? 'N/A'); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo htmlspecialchars($nv->chuc_vu ?? 'Chưa xác định'); ?>
                                            </span>
                                            <br />
                                            <small><?php echo htmlspecialchars($nv->trinh_do ?? ''); ?></small>
                                        </td>

                                        <td class="gio-vao-<?php echo $nv->id; ?>">
                                            <?php if (!empty($nv->gio_vao)): ?>
                                                <span class="text-success font-weight-bold">
                                                    <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($nv->gio_vao)); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">--:--</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="gio-ra-<?php echo $nv->id; ?>">
                                            <?php if (!empty($nv->gio_ra)): ?>
                                                <span class="text-danger font-weight-bold">
                                                    <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($nv->gio_ra)); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">--:--</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php if (!empty($nv->trangthai)): ?>
                                                <?php
                                                $badge_class = 'secondary';
                                                switch($nv->trangthai) {
                                                    case 'DiLam': $badge_class = 'success'; break;
                                                    case 'DiTre': $badge_class = 'warning'; break;
                                                    case 'NghiPhep': $badge_class = 'info'; break;
                                                    case 'NghiKhongPhep': $badge_class = 'danger'; break;
                                                    case 'TangCa': $badge_class = 'primary'; break;
                                                }
                                                ?>
                                                <span class="badge badge-<?php echo $badge_class; ?>">
                                                    <?php echo htmlspecialchars($nv->trangthai); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Chưa chấm</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="project-actions text-center action-buttons-<?php echo $nv->id; ?>">
                                            <?php if (empty($nv->gio_vao)): ?>
                                                <button type="button" onclick="chamCong(<?php echo $nv->id; ?>, 'checkin')"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="fas fa-sign-in-alt"></i> Vào ca
                                                </button>

                                            <?php elseif (empty($nv->gio_ra)): ?>
                                                <button type="button" onclick="chamCong(<?php echo $nv->id; ?>, 'checkout')"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-sign-out-alt"></i> Ra ca
                                                </button>

                                            <?php else: ?>
                                                <button type="button" class="btn btn-success btn-sm" disabled>
                                                    <i class="fas fa-check"></i> Hoàn tất
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <p>Chưa có dữ liệu nhân viên</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function chamCong(nvId, action) {
    // Hiển thị loading trên nút
    const btnContainer = document.querySelector('.action-buttons-' + nvId);
    const originalBtn = btnContainer.innerHTML;
    btnContainer.innerHTML = '<button class="btn btn-secondary btn-sm" disabled><i class="fas fa-spinner fa-spin"></i> Đang xử lý...</button>';
    
    // Tạo FormData
    const formData = new FormData();
    formData.append('nhanvien_id', nvId);
    formData.append('action', action);
    
    // Gửi AJAX request đến file riêng
    fetch('./pages/chamcong_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Kiểm tra response có phải JSON không
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server không trả về JSON. Kiểm tra file chamcong_ajax.php');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Hiển thị thông báo thành công
            showNotification(data.message, 'success');
            
            // Cập nhật giao diện
            if (data.action === 'checkin') {
                // Cập nhật giờ vào
                document.querySelector('.gio-vao-' + nvId).innerHTML = 
                    '<span class="text-success font-weight-bold"><i class="fas fa-clock"></i> ' + data.time + '</span>';
                
                // Đổi nút thành "Ra ca"
                btnContainer.innerHTML = 
                    '<button type="button" onclick="chamCong(' + nvId + ', \'checkout\')" class="btn btn-warning btn-sm">' +
                    '<i class="fas fa-sign-out-alt"></i> Ra ca</button>';
                
            } else if (data.action === 'checkout') {
                // Cập nhật giờ ra
                document.querySelector('.gio-ra-' + nvId).innerHTML = 
                    '<span class="text-danger font-weight-bold"><i class="fas fa-clock"></i> ' + data.time + '</span>';
                
                // Đổi nút thành "Hoàn tất"
                btnContainer.innerHTML = 
                    '<button type="button" class="btn btn-success btn-sm" disabled>' +
                    '<i class="fas fa-check"></i> Hoàn tất</button>';
            }
        } else {
            // Hiển thị thông báo lỗi
            showNotification(data.message, 'danger');
            // Khôi phục nút cũ nếu lỗi
            btnContainer.innerHTML = originalBtn;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra: ' + error.message, 'danger');
        btnContainer.innerHTML = originalBtn;
    });
}

function showNotification(message, type) {
    const notificationArea = document.getElementById('notification-area');
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-' + type + ' alert-dismissible fade show';
    alertDiv.innerHTML = `
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}
    `;
    
    notificationArea.appendChild(alertDiv);
    
    // Tự động ẩn sau 5 giây
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 150);
    }, 5000);
}
</script>

<style>
#notification-area {
    position: relative;
    z-index: 1050;
}

#notification-area .alert {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>