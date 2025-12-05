<?php
// Include file Database class
require_once __DIR__ . '/../config/database.php';

// Khởi tạo Database object
$db = new Database();

// Lấy tham số lọc từ URL
$filter_nv = isset($_GET['nhanvien']) ? intval($_GET['nhanvien']) : 0;
$filter_from = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01'); // Đầu tháng
$filter_to = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d'); // Hôm nay

// Validate dates
if (!strtotime($filter_from)) $filter_from = date('Y-m-01');
if (!strtotime($filter_to)) $filter_to = date('Y-m-d');

// --- LẤY DANH SÁCH NHÂN VIÊN (cho dropdown) ---
try {
    $db->query("SELECT id, CONCAT(ho, ' ', ten) as ho_ten FROM nhanvien ORDER BY ho, ten");
    $list_nhanvien = $db->resultSet();
} catch (Exception $e) {
    $list_nhanvien = [];
}

// --- LẤY LỊCH SỬ CHẤM CÔNG ---
try {
    $sql = "SELECT 
                cc.id,
                cc.id_nv,
                CONCAT(nv.ho, ' ', nv.ten) as ho_ten,
                nv.sdt,
                r.ten as chuc_vu,
                cc.ngay,
                cc.gio_vao,
                cc.gio_ra,
                cc.trangthai,
                CASE 
                    WHEN cc.gio_vao IS NOT NULL AND cc.gio_ra IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, cc.gio_vao, cc.gio_ra)
                    ELSE NULL
                END as so_gio_lam
            FROM chamcong cc
            INNER JOIN nhanvien nv ON cc.id_nv = nv.id
            LEFT JOIN role r ON nv.id_role = r.id
            WHERE cc.ngay BETWEEN :from_date AND :to_date";
    
    // Thêm điều kiện lọc theo nhân viên nếu có
    if ($filter_nv > 0) {
        $sql .= " AND cc.id_nv = :nhanvien_id";
    }
    
    $sql .= " ORDER BY cc.ngay DESC, nv.ho, nv.ten";
    
    $db->query($sql);
    $db->bind(':from_date', $filter_from);
    $db->bind(':to_date', $filter_to);
    
    if ($filter_nv > 0) {
        $db->bind(':nhanvien_id', $filter_nv);
    }
    
    $history = $db->resultSet();
} catch (Exception $e) {
    $history = [];
    $error_message = "Lỗi truy vấn: " . $e->getMessage();
}

// --- THỐNG KÊ TỔNG QUAN ---
$total_records = count($history);
$total_dilam = 0;
$total_ditre = 0;
$total_nghiphep = 0;
$total_gio_lam = 0;

foreach ($history as $item) {
    if ($item->trangthai == 'DiLam') $total_dilam++;
    if ($item->trangthai == 'DiTre') $total_ditre++;
    if ($item->trangthai == 'NghiPhep') $total_nghiphep++;
    if ($item->so_gio_lam) $total_gio_lam += $item->so_gio_lam;
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Lịch Sử Chấm Công</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item">Chấm công</li>
                        <li class="breadcrumb-item active">Lịch sử</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            
            <!-- Thông báo -->
            <div id="notification-area"></div>
            
            <!-- Bộ lọc -->
            <div class="card card-primary collapsed-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter"></i> Bộ lọc tìm kiếm
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="index.php">
                        <input type="hidden" name="page" value="lichsu_chamcong">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Nhân viên</label>
                                    <select name="nhanvien" class="form-control">
                                        <option value="0">-- Tất cả nhân viên --</option>
                                        <?php foreach ($list_nhanvien as $nv): ?>
                                            <option value="<?php echo $nv->id; ?>" 
                                                <?php echo ($filter_nv == $nv->id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($nv->ho_ten); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Từ ngày</label>
                                    <input type="date" name="from_date" class="form-control" 
                                        value="<?php echo htmlspecialchars($filter_from); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Đến ngày</label>
                                    <input type="date" name="to_date" class="form-control" 
                                        value="<?php echo htmlspecialchars($filter_to); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Thống kê tổng quan -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $total_records; ?></h3>
                            <p>Tổng bản ghi</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $total_dilam; ?></h3>
                            <p>Đi làm đúng giờ</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $total_ditre; ?></h3>
                            <p>Đi trễ</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?php echo $total_gio_lam; ?></h3>
                            <p>Tổng giờ làm việc</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Bảng lịch sử -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Lịch sử chấm công 
                        (<?php echo date('d/m/Y', strtotime($filter_from)); ?> - <?php echo date('d/m/Y', strtotime($filter_to)); ?>)
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" onclick="exportExcel()">
                            <i class="fas fa-file-excel"></i> Xuất Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="exportPDF()">
                            <i class="fas fa-file-pdf"></i> Xuất PDF
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="historyTable">
                            <thead>
                                <tr>
                                    <th style="width: 5%">STT</th>
                                    <th style="width: 10%">Ngày</th>
                                    <th style="width: 20%">Nhân viên</th>
                                    <th style="width: 12%">Chức vụ</th>
                                    <th style="width: 10%">Giờ vào</th>
                                    <th style="width: 10%">Giờ ra</th>
                                    <th style="width: 10%">Số giờ</th>
                                    <th style="width: 13%">Trạng thái</th>
                                    <th style="width: 10%" class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($history) > 0): ?>
                                    <?php 
                                    $stt = 1;
                                    foreach ($history as $item): 
                                    ?>
                                        <tr id="row-<?php echo $item->id; ?>">
                                            <td><?php echo $stt++; ?></td>
                                            <td>
                                                <strong><?php echo date('d/m/Y', strtotime($item->ngay)); ?></strong>
                                                <br>
                                                
                                                <small class="text-muted">
                                                    <?php 
                                                    $formatter = new IntlDateFormatter(
                                                        'vi_VN',
                                                        IntlDateFormatter::FULL,
                                                        IntlDateFormatter::NONE,
                                                        'Asia/Ho_Chi_Minh',
                                                        IntlDateFormatter::GREGORIAN,
                                                        'EEEE'
                                                    );
                                                    echo $formatter->format(strtotime($item->ngay));
                                                    ?>
                                                </small>

                                            </td>
                                            <td>
                                                <a href="index.php?page=nhanvien_profile&id=<?php echo $item->id_nv; ?>">
                                                    <?php echo htmlspecialchars($item->ho_ten); ?>
                                                </a>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($item->sdt ?? 'N/A'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php echo htmlspecialchars($item->chuc_vu ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($item->gio_vao): ?>
                                                    <span class="text-success">
                                                        <i class="fas fa-sign-in-alt"></i>
                                                        <?php echo date('H:i', strtotime($item->gio_vao)); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">--:--</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($item->gio_ra): ?>
                                                    <span class="text-danger">
                                                        <i class="fas fa-sign-out-alt"></i>
                                                        <?php echo date('H:i', strtotime($item->gio_ra)); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">--:--</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($item->so_gio_lam): ?>
                                                    <span class="badge badge-primary">
                                                        <?php echo $item->so_gio_lam; ?> giờ
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $badge_class = 'secondary';
                                                switch($item->trangthai) {
                                                    case 'DiLam': $badge_class = 'success'; break;
                                                    case 'DiTre': $badge_class = 'warning'; break;
                                                    case 'NghiPhep': $badge_class = 'info'; break;
                                                    case 'NghiKhongPhep': $badge_class = 'danger'; break;
                                                    case 'TangCa': $badge_class = 'primary'; break;
                                                }
                                                ?>
                                                <span class="badge badge-<?php echo $badge_class; ?>">
                                                    <?php echo $item->trangthai ?? 'N/A'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-info btn-xs" 
                                                    onclick="viewDetail(<?php echo $item->id; ?>)"
                                                    title="Chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-xs" 
                                                    onclick="deleteRecord(<?php echo $item->id; ?>)"
                                                    title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p>Không có dữ liệu chấm công trong khoảng thời gian này</p>
                                            <a href="index.php?page=chamcong" class="btn btn-primary btn-sm">
                                                <i class="fas fa-clock"></i> Đi tới chấm công
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Chi tiết -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Chi tiết chấm công
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Đang tải...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
// Xem chi tiết
function viewDetail(id) {
    $('#detailModal').modal('show');
    
    fetch('./pages/chamcong_detail_api.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.data;
                const soPhutLam = item.so_phut_lam || 0;
                const soGio = Math.floor(soPhutLam / 60);
                const soPhut = soPhutLam % 60;
                
                let trangThaiBadge = 'secondary';
                switch(item.trangthai) {
                    case 'DiLam': trangThaiBadge = 'success'; break;
                    case 'DiTre': trangThaiBadge = 'warning'; break;
                    case 'NghiPhep': trangThaiBadge = 'info'; break;
                    case 'NghiKhongPhep': trangThaiBadge = 'danger'; break;
                    case 'TangCa': trangThaiBadge = 'primary'; break;
                }
                
                $('#detailContent').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-user"></i> Thông tin nhân viên</h5>
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th width="40%">Họ tên:</th>
                                    <td><strong>${item.ho_ten}</strong></td>
                                </tr>
                                <tr>
                                    <th>Chức vụ:</th>
                                    <td><span class="badge badge-info">${item.chuc_vu || 'N/A'}</span></td>
                                </tr>
                                <tr>
                                    <th>Trình độ:</th>
                                    <td>${item.trinh_do || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Số điện thoại:</th>
                                    <td><i class="fas fa-phone"></i> ${item.sdt || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Địa chỉ:</th>
                                    <td>${item.diachi || 'N/A'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-clock"></i> Thông tin chấm công</h5>
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th width="40%">Ngày:</th>
                                    <td><strong>${new Date(item.ngay).toLocaleDateString('vi-VN')}</strong></td>
                                </tr>
                                <tr>
                                    <th>Giờ vào:</th>
                                    <td><span class="text-success"><i class="fas fa-sign-in-alt"></i> ${item.gio_vao ? item.gio_vao.substring(0,5) : '--:--'}</span></td>
                                </tr>
                                <tr>
                                    <th>Giờ ra:</th>
                                    <td><span class="text-danger"><i class="fas fa-sign-out-alt"></i> ${item.gio_ra ? item.gio_ra.substring(0,5) : '--:--'}</span></td>
                                </tr>
                                <tr>
                                    <th>Thời gian làm:</th>
                                    <td><span class="badge badge-primary">${soGio}h ${soPhut}p</span></td>
                                </tr>
                                <tr>
                                    <th>Trạng thái:</th>
                                    <td><span class="badge badge-${trangThaiBadge}">${item.trangthai || 'N/A'}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                `);
            } else {
                $('#detailContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${data.message}
                    </div>
                `);
            }
        })
        .catch(error => {
            $('#detailContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra: ${error}
                </div>
            `);
        });
}

// Xóa bản ghi
function deleteRecord(id) {
    if (confirm('Bạn có chắc chắn muốn xóa bản ghi chấm công này?\nHành động này không thể hoàn tác!')) {
        const formData = new FormData();
        formData.append('id', id);
        
        fetch('./pages/chamcong_delete_api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Xóa dòng khỏi table
                document.getElementById('row-' + id).remove();
                
                // Reload sau 1.5s
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification(data.message, 'danger');
            }
        })
        .catch(error => {
            showNotification('Có lỗi xảy ra: ' + error, 'danger');
        });
    }
}

// Xuất Excel
function exportExcel() {
    const table = document.getElementById('historyTable');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Lịch sử chấm công"});
    XLSX.writeFile(wb, 'LichSuChamCong_' + new Date().getTime() + '.xlsx');
    showNotification('Xuất Excel thành công!', 'success');
}

// Xuất PDF
function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');
    
    // Thêm font hỗ trợ tiếng Việt (nếu có)
    doc.setFont('helvetica');
    doc.setFontSize(16);
    doc.text('LỊCH SỬ CHẤM CÔNG', 148, 15, { align: 'center' });
    
    doc.setFontSize(10);
    doc.text('Ngày xuất: ' + new Date().toLocaleDateString('vi-VN'), 20, 25);
    
    // Lấy dữ liệu từ table
    const table = document.getElementById('historyTable');
    const rows = [];
    
    // Header
    const headers = ['STT', 'Ngày', 'Nhân viên', 'Chức vụ', 'Giờ vào', 'Giờ ra', 'Số giờ', 'Trạng thái'];
    rows.push(headers);
    
    // Data rows
    const tbody = table.querySelector('tbody');
    const trs = tbody.querySelectorAll('tr');
    trs.forEach(tr => {
        const tds = tr.querySelectorAll('td');
        if (tds.length > 1) {
            const row = [];
            for (let i = 0; i < 8; i++) {
                row.push(tds[i].innerText.trim().replace(/\n/g, ' '));
            }
            rows.push(row);
        }
    });
    
    // Vẽ table
    doc.autoTable({
        head: [rows[0]],
        body: rows.slice(1),
        startY: 30,
        styles: { fontSize: 8, font: 'helvetica' },
        headStyles: { fillColor: [41, 128, 185] }
    });
    
    doc.save('LichSuChamCong_' + new Date().getTime() + '.pdf');
    showNotification('Xuất PDF thành công!', 'success');
}

// Hiển thị thông báo
function showNotification(message, type) {
    const notificationArea = document.getElementById('notification-area');
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-' + type + ' alert-dismissible fade show';
    alertDiv.innerHTML = `
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}
    `;
    
    notificationArea.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 150);
    }, 3000);
}
</script>

<style>
.table-responsive {
    overflow-x: auto;
}

.small-box {
    cursor: pointer;
    transition: transform 0.2s;
}

.small-box:hover {
    transform: translateY(-5px);
}

.btn-xs {
    padding: 2px 6px;
    font-size: 12px;
}

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