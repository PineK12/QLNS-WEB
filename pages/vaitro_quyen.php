<?php
// Include file Database class
require_once __DIR__ . '/../config/database.php';

// Khởi tạo Database object
$db = new Database();

// Xử lý thêm vai trò mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_role') {
    $ten_role = trim($_POST['ten_role']);
    $mota = trim($_POST['mota']);

    if (!empty($ten_role)) {
        try {
            $db->query("INSERT INTO role (ten, mota) VALUES (:ten, :mota)");
            $db->bind(':ten', $ten_role);
            $db->bind(':mota', $mota);
            $db->execute();

            $success_message = "Thêm vai trò mới thành công!";
        } catch (Exception $e) {
            $error_message = "Lỗi: " . $e->getMessage();
        }
    } else {
        $error_message = "Tên vai trò không được để trống!";
    }
}

// Xử lý cập nhật vai trò
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    $id = intval($_POST['role_id']);
    $ten_role = trim($_POST['ten_role_edit']);
    $mota = trim($_POST['mota_edit']);

    if (!empty($ten_role) && $id > 0) {
        try {
            $db->query("UPDATE role SET ten = :ten, mota = :mota WHERE id = :id");
            $db->bind(':ten', $ten_role);
            $db->bind(':mota', $mota);
            $db->bind(':id', $id);
            $db->execute();

            $success_message = "Cập nhật vai trò thành công!";
        } catch (Exception $e) {
            $error_message = "Lỗi: " . $e->getMessage();
        }
    }
}

// Lấy danh sách vai trò
try {
    $db->query("SELECT r.*, COUNT(nv.id) as so_nhanvien 
                FROM role r 
                LEFT JOIN nhanvien nv ON r.id = nv.id_role 
                GROUP BY r.id, r.ten, r.mota
                ORDER BY r.id");
    $list_roles = $db->resultSet();
} catch (Exception $e) {
    $list_roles = [];
    $error_message = "Lỗi truy vấn: " . $e->getMessage();
}

// Thống kê
$tong_role = count($list_roles);
$tong_nhanvien = 0;
foreach ($list_roles as $role) {
    $tong_nhanvien += $role->so_nhanvien;
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Vai Trò & Quyền</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Vai trò & Quyền</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- Thông báo -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Thống kê -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $tong_role; ?></h3>
                            <p>Tổng vai trò</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $tong_nhanvien; ?></h3>
                            <p>Tổng nhân viên</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 style="color: white;">
                                <?php echo $tong_nhanvien > 0 ? number_format($tong_nhanvien / $tong_role, 1) : 0; ?>
                            </h3>
                            <p style="color: white;">Trung bình / vai trò</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nút thêm vai trò -->
            <div class="row mb-3">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addRoleModal">
                        <i class="fas fa-plus"></i> Thêm vai trò mới
                    </button>
                </div>
            </div>

            <!-- Danh sách vai trò dạng card -->
            <div class="row">
                <?php if (count($list_roles) > 0): ?>
                    <?php foreach ($list_roles as $role): ?>
                        <div class="col-lg-4 col-md-6" id="role-card-<?php echo $role->id; ?>">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-user-shield"></i>
                                        <strong><?php echo htmlspecialchars($role->ten); ?></strong>
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool"
                                            onclick="editRole(<?php echo $role->id; ?>, '<?php echo addslashes($role->ten); ?>', '<?php echo addslashes($role->mota ?? ''); ?>')">
                                            <i class="fas fa-edit text-primary"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool"
                                            onclick="deleteRole(<?php echo $role->id; ?>, '<?php echo addslashes($role->ten); ?>')">
                                            <i class="fas fa-trash text-danger"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">
                                        <?php echo $role->mota ? htmlspecialchars($role->mota) : '<em>Chưa có mô tả</em>'; ?>
                                    </p>

                                    <div class="info-box bg-light">
                                        <span class="info-box-icon bg-info">
                                            <i class="fas fa-users"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Số nhân viên</span>
                                            <span class="info-box-number"><?php echo $role->so_nhanvien; ?> người</span>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <a href="index.php?page=nhanvien_list&role=<?php echo $role->id; ?>"
                                            class="btn btn-sm btn-outline-primary btn-block">
                                            <i class="fas fa-eye"></i> Xem danh sách nhân viên
                                        </a>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <small class="text-muted">
                                        <i class="fas fa-key"></i> ID: <?php echo $role->id; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Chưa có vai trò nào trong hệ thống.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Bảng phân quyền chi tiết -->
            <div class="card">
                <div class="card-header bg-gradient-primary">
                    <h3 class="card-title">
                        <i class="fas fa-shield-alt"></i> Ma trận phân quyền
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 30%">Chức năng</th>
                                    <?php foreach ($list_roles as $role): ?>
                                        <th class="text-center"><?php echo htmlspecialchars($role->ten); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $chucnang = [
                                    'Dashboard' => ['icon' => 'tachometer-alt', 'admin' => true, 'quanly' => true, 'nhanvien' => true],
                                    'Quản lý nhân viên' => ['icon' => 'users', 'admin' => true, 'quanly' => true, 'nhanvien' => false],
                                    'Thêm nhân viên' => ['icon' => 'user-plus', 'admin' => true, 'quanly' => false, 'nhanvien' => false],
                                    'Sửa nhân viên' => ['icon' => 'user-edit', 'admin' => true, 'quanly' => true, 'nhanvien' => false],
                                    'Xóa nhân viên' => ['icon' => 'user-times', 'admin' => true, 'quanly' => false, 'nhanvien' => false],
                                    'Chấm công' => ['icon' => 'clock', 'admin' => true, 'quanly' => true, 'nhanvien' => true],
                                    'Xem lịch sử chấm công' => ['icon' => 'history', 'admin' => true, 'quanly' => true, 'nhanvien' => true],
                                    'Xóa chấm công' => ['icon' => 'trash', 'admin' => true, 'quanly' => false, 'nhanvien' => false],
                                    'Báo cáo chấm công' => ['icon' => 'chart-bar', 'admin' => true, 'quanly' => true, 'nhanvien' => false],
                                    'Quản lý lương' => ['icon' => 'money-bill-wave', 'admin' => true, 'quanly' => true, 'nhanvien' => false],
                                    'Xem bảng lương' => ['icon' => 'file-invoice-dollar', 'admin' => true, 'quanly' => true, 'nhanvien' => true],
                                    'Tính lương' => ['icon' => 'calculator', 'admin' => true, 'quanly' => false, 'nhanvien' => false],
                                    'Báo cáo tổng hợp' => ['icon' => 'chart-line', 'admin' => true, 'quanly' => true, 'nhanvien' => false],
                                    'Vai trò & Quyền' => ['icon' => 'user-tag', 'admin' => true, 'quanly' => false, 'nhanvien' => false],
                                    'Cài đặt hệ thống' => ['icon' => 'cogs', 'admin' => true, 'quanly' => false, 'nhanvien' => false],
                                ];

                                foreach ($chucnang as $ten => $info):
                                    ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-<?php echo $info['icon']; ?> text-primary"></i>
                                            <?php echo $ten; ?>
                                        </td>
                                        <?php foreach ($list_roles as $role): ?>
                                            <td class="text-center">
                                                <?php
                                                $role_key = strtolower($role->ten);
                                                $has_permission = $info[$role_key] ?? false;

                                                if ($has_permission) {
                                                    echo '<i class="fas fa-check-circle text-success fa-lg"></i>';
                                                } else {
                                                    echo '<i class="fas fa-times-circle text-danger"></i>';
                                                }
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Ma trận phân quyền mặc định của hệ thống.
                        Có thể tùy chỉnh chi tiết hơn trong phiên bản nâng cao.
                    </small>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Modal Thêm vai trò -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=vaitro_quyen">
                <input type="hidden" name="action" value="add_role">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> Thêm vai trò mới
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên vai trò <span class="text-danger">*</span></label>
                        <input type="text" name="ten_role" class="form-control" required
                            placeholder="Ví dụ: Kế toán, Nhân sự, ...">
                    </div>
                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="mota" class="form-control" rows="3"
                            placeholder="Mô tả chi tiết về vai trò này..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu vai trò
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa vai trò -->
<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=vaitro_quyen">
                <input type="hidden" name="action" value="update_role">
                <input type="hidden" name="role_id" id="edit_role_id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Chỉnh sửa vai trò
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên vai trò <span class="text-danger">*</span></label>
                        <input type="text" name="ten_role_edit" id="edit_ten_role" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="mota_edit" id="edit_mota" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Sửa vai trò
    function editRole(id, ten, mota) {
        document.getElementById('edit_role_id').value = id;
        document.getElementById('edit_ten_role').value = ten;
        document.getElementById('edit_mota').value = mota;
        $('#editRoleModal').modal('show');
    }

    // Xóa vai trò
    function deleteRole(id, ten) {
        if (confirm('Bạn có chắc chắn muốn xóa vai trò "' + ten + '"?\n\nLưu ý: Tất cả nhân viên thuộc vai trò này sẽ không có vai trò!')) {
            const formData = new FormData();
            formData.append('id', id);

            fetch('./pages/role_delete_api.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Xóa card khỏi giao diện
                        document.getElementById('role-card-' + id).remove();

                        // Hiển thị thông báo
                        showAlert('success', data.message);

                        // Reload sau 1.5s
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert('danger', data.message);
                    }
                })
                .catch(error => {
                    showAlert('danger', 'Có lỗi xảy ra: ' + error);
                });
        }
    }

    // Hiển thị thông báo
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-' + type + ' alert-dismissible fade show';
        alertDiv.innerHTML = `
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}
    `;

        document.querySelector('.content').insertBefore(
            alertDiv,
            document.querySelector('.content').firstChild
        );

        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 3000);
    }
</script>

<style>
    .card-outline {
        border-top: 3px solid;
    }

    .info-box {
        min-height: 80px;
    }

    .small-box:hover {
        transform: translateY(-5px);
        transition: transform 0.3s;
    }

    .card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s;
    }
</style>