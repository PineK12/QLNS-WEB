<?php
// Include file Database class
require_once __DIR__ . '/../config/database.php';

// Khởi tạo Database object
$db = new Database();

// Lấy tham số lọc từ URL
$filter_thang = isset($_GET['thang']) ? intval($_GET['thang']) : date('n');
$filter_nam = isset($_GET['nam']) ? intval($_GET['nam']) : date('Y');
$filter_phong = isset($_GET['phongban']) ? intval($_GET['phongban']) : 0;

// Validate tháng và năm
if ($filter_thang < 1 || $filter_thang > 12) $filter_thang = date('n');
if ($filter_nam < 2020 || $filter_nam > 2030) $filter_nam = date('Y');

// --- LẤY DANH SÁCH PHÒNG BAN (nếu có) ---
try {
    $db->query("SELECT DISTINCT r.id, r.ten FROM role r 
                INNER JOIN nhanvien nv ON r.id = nv.id_role 
                ORDER BY r.ten");
    $list_phongban = $db->resultSet();
} catch (Exception $e) {
    $list_phongban = [];
}

// --- BÁO CÁO THEO NHÂN VIÊN ---
try {
    $sql = "SELECT 
                nv.id,
                CONCAT(nv.ho, ' ', nv.ten) as ho_ten,
                r.ten as chuc_vu,
                COUNT(DISTINCT cc.ngay) as tong_ngay_cong,
                SUM(CASE WHEN cc.trangthai = 'DiLam' THEN 1 ELSE 0 END) as ngay_dilam,
                SUM(CASE WHEN cc.trangthai = 'DiTre' THEN 1 ELSE 0 END) as ngay_ditre,
                SUM(CASE WHEN cc.trangthai = 'NghiPhep' THEN 1 ELSE 0 END) as ngay_nghiphep,
                SUM(CASE WHEN cc.trangthai = 'NghiKhongPhep' THEN 1 ELSE 0 END) as ngay_nghikhongphep,
                SUM(CASE WHEN cc.trangthai = 'TangCa' THEN 1 ELSE 0 END) as ngay_tangca,
                SUM(CASE 
                    WHEN cc.gio_vao IS NOT NULL AND cc.gio_ra IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, cc.gio_vao, cc.gio_ra)
                    ELSE 0
                END) as tong_phut_lam,
                nv.luongcoban
            FROM nhanvien nv
            LEFT JOIN role r ON nv.id_role = r.id
            LEFT JOIN chamcong cc ON nv.id = cc.id_nv 
                AND MONTH(cc.ngay) = :thang 
                AND YEAR(cc.ngay) = :nam
            WHERE 1=1";
    
    if ($filter_phong > 0) {
        $sql .= " AND nv.id_role = :phongban";
    }
    
    $sql .= " GROUP BY nv.id, nv.ho, nv.ten, r.ten, nv.luongcoban
              ORDER BY nv.ho, nv.ten";
    
    $db->query($sql);
    $db->bind(':thang', $filter_thang);
    $db->bind(':nam', $filter_nam);
    
    if ($filter_phong > 0) {
        $db->bind(':phongban', $filter_phong);
    }
    
    $baocao_nhanvien = $db->resultSet();
} catch (Exception $e) {
    $baocao_nhanvien = [];
    $error_message = "Lỗi truy vấn: " . $e->getMessage();
}

// --- THỐNG KÊ TỔNG QUAN ---
$tong_nhanvien = count($baocao_nhanvien);
$tong_ngay_dilam = 0;
$tong_ngay_ditre = 0;
$tong_ngay_nghiphep = 0;
$tong_ngay_nghikhongphep = 0;
$tong_gio_lam = 0;

foreach ($baocao_nhanvien as $nv) {
    $tong_ngay_dilam += $nv->ngay_dilam;
    $tong_ngay_ditre += $nv->ngay_ditre;
    $tong_ngay_nghiphep += $nv->ngay_nghiphep;
    $tong_ngay_nghikhongphep += $nv->ngay_nghikhongphep;
    $tong_gio_lam += floor($nv->tong_phut_lam / 60);
}

// Số ngày làm việc trong tháng (tính cả thứ 7 CN)
$so_ngay_thang = cal_days_in_month(CAL_GREGORIAN, $filter_thang, $filter_nam);

// --- THỐNG KÊ THEO TRẠNG THÁI (cho biểu đồ) ---
try {
    $db->query("SELECT 
                    trangthai,
                    COUNT(*) as so_luong
                FROM chamcong
                WHERE MONTH(ngay) = :thang AND YEAR(ngay) = :nam
                GROUP BY trangthai");
    $db->bind(':thang', $filter_thang);
    $db->bind(':nam', $filter_nam);
    $chart_trangthai = $db->resultSet();
    
    $chart_labels = [];
    $chart_data = [];
    $chart_colors = [];
    
    $color_map = [
        'DiLam' => '#28a745',
        'DiTre' => '#ffc107',
        'NghiPhep' => '#17a2b8',
        'NghiKhongPhep' => '#dc3545',
        'TangCa' => '#007bff'
    ];
    
    foreach ($chart_trangthai as $item) {
        $chart_labels[] = $item->trangthai;
        $chart_data[] = $item->so_luong;
        $chart_colors[] = $color_map[$item->trangthai] ?? '#6c757d';
    }
    
    $chartLabelsJSON = json_encode($chart_labels);
    $chartDataJSON = json_encode($chart_data);
    $chartColorsJSON = json_encode($chart_colors);
} catch (Exception $e) {
    $chartLabelsJSON = '[]';
    $chartDataJSON = '[]';
    $chartColorsJSON = '[]';
}

// --- THỐNG KÊ THEO NGÀY (cho biểu đồ line) ---
try {
    $db->query("SELECT 
                    DATE(ngay) as ngay,
                    COUNT(*) as so_luong
                FROM chamcong
                WHERE MONTH(ngay) = :thang AND YEAR(ngay) = :nam
                GROUP BY DATE(ngay)
                ORDER BY ngay");
    $db->bind(':thang', $filter_thang);
    $db->bind(':nam', $filter_nam);
    $chart_ngay = $db->resultSet();
    
    $line_labels = [];
    $line_data = [];
    
    foreach ($chart_ngay as $item) {
        $line_labels[] = date('d/m', strtotime($item->ngay));
        $line_data[] = $item->so_luong;
    }
    
    $lineLabelsJSON = json_encode($line_labels);
    $lineDataJSON = json_encode($line_data);
} catch (Exception $e) {
    $lineLabelsJSON = '[]';
    $lineDataJSON = '[]';
}

// Hàm tính lương dự kiến
function tinhLuongDuKien($luongcoban, $ngay_dilam, $ngay_ditre, $ngay_tangca, $so_ngay_thang) {
    $luong_1_ngay = $luongcoban / $so_ngay_thang;
    $luong_dilam = $ngay_dilam * $luong_1_ngay;
    $luong_ditre = $ngay_ditre * $luong_1_ngay * 0.8; // Trừ 20%
    $luong_tangca = $ngay_tangca * $luong_1_ngay * 1.5; // Cộng 50%
    return $luong_dilam + $luong_ditre + $luong_tangca;
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Báo Cáo Chấm Công</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item">Chấm công</li>
                        <li class="breadcrumb-item active">Báo cáo</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            
            <!-- Bộ lọc -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter"></i> Chọn tháng báo cáo
                    </h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="index.php">
                        <input type="hidden" name="page" value="baocao_chamcong">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tháng</label>
                                    <select name="thang" class="form-control">
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?php echo $i; ?>" 
                                                <?php echo ($filter_thang == $i) ? 'selected' : ''; ?>>
                                                Tháng <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Năm</label>
                                    <select name="nam" class="form-control">
                                        <?php for ($i = 2020; $i <= 2030; $i++): ?>
                                            <option value="<?php echo $i; ?>" 
                                                <?php echo ($filter_nam == $i) ? 'selected' : ''; ?>>
                                                <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Phòng ban</label>
                                    <select name="phongban" class="form-control">
                                        <option value="0">-- Tất cả phòng ban --</option>
                                        <?php foreach ($list_phongban as $pb): ?>
                                            <option value="<?php echo $pb->id; ?>" 
                                                <?php echo ($filter_phong == $pb->id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($pb->ten); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-chart-bar"></i> Xem báo cáo
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
                            <h3><?php echo $tong_nhanvien; ?></h3>
                            <p>Tổng nhân viên</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $tong_ngay_dilam; ?></h3>
                            <p>Tổng ngày đi làm</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $tong_ngay_ditre; ?></h3>
                            <p>Tổng ngày đi trễ</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?php echo number_format($tong_gio_lam); ?></h3>
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

            <!-- Biểu đồ -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie"></i> Phân bố trạng thái chấm công
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line"></i> Số lượng chấm công theo ngày
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="dailyChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bảng báo cáo chi tiết -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i> Báo cáo chi tiết tháng <?php echo $filter_thang; ?>/<?php echo $filter_nam; ?>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" onclick="exportExcel()">
                            <i class="fas fa-file-excel"></i> Xuất Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="exportPDF()">
                            <i class="fas fa-file-pdf"></i> Xuất PDF
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="printReport()">
                            <i class="fas fa-print"></i> In báo cáo
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered" id="reportTable">
                            <thead class="bg-primary">
                                <tr>
                                    <th rowspan="2" class="align-middle text-center" style="width: 5%">STT</th>
                                    <th rowspan="2" class="align-middle" style="width: 20%">Nhân viên</th>
                                    <th rowspan="2" class="align-middle text-center" style="width: 10%">Chức vụ</th>
                                    <th colspan="5" class="text-center">Chi tiết công (ngày)</th>
                                    <th rowspan="2" class="align-middle text-center" style="width: 10%">Tổng giờ</th>
                                    <th rowspan="2" class="align-middle text-center" style="width: 12%">Lương dự kiến</th>
                                </tr>
                                <tr>
                                    <th class="text-center bg-success" style="width: 8%">Đi làm</th>
                                    <th class="text-center bg-warning" style="width: 8%">Đi trễ</th>
                                    <th class="text-center bg-info" style="width: 8%">Nghỉ phép</th>
                                    <th class="text-center bg-danger" style="width: 8%">Nghỉ KP</th>
                                    <th class="text-center bg-primary" style="width: 8%">Tăng ca</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($baocao_nhanvien) > 0): ?>
                                    <?php 
                                    $stt = 1;
                                    $tong_luong_dukien = 0;
                                    foreach ($baocao_nhanvien as $nv): 
                                        $so_gio = floor($nv->tong_phut_lam / 60);
                                        $so_phut = $nv->tong_phut_lam % 60;
                                        $luong_dukien = tinhLuongDuKien(
                                            $nv->luongcoban, 
                                            $nv->ngay_dilam, 
                                            $nv->ngay_ditre, 
                                            $nv->ngay_tangca, 
                                            $so_ngay_thang
                                        );
                                        $tong_luong_dukien += $luong_dukien;
                                    ?>
                                        <tr>
                                            <td class="text-center"><?php echo $stt++; ?></td>
                                            <td>
                                                <a href="index.php?page=nhanvien_profile&id=<?php echo $nv->id; ?>">
                                                    <strong><?php echo htmlspecialchars($nv->ho_ten); ?></strong>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info">
                                                    <?php echo htmlspecialchars($nv->chuc_vu ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-success"><?php echo $nv->ngay_dilam; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-warning"><?php echo $nv->ngay_ditre; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info"><?php echo $nv->ngay_nghiphep; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-danger"><?php echo $nv->ngay_nghikhongphep; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary"><?php echo $nv->ngay_tangca; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <strong><?php echo $so_gio; ?>h <?php echo $so_phut; ?>p</strong>
                                            </td>
                                            <td class="text-right">
                                                <strong class="text-success">
                                                    <?php echo number_format($luong_dukien, 0, ',', '.'); ?> đ
                                                </strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="3" class="text-right">TỔNG CỘNG:</td>
                                        <td class="text-center"><?php echo $tong_ngay_dilam; ?></td>
                                        <td class="text-center"><?php echo $tong_ngay_ditre; ?></td>
                                        <td class="text-center"><?php echo $tong_ngay_nghiphep; ?></td>
                                        <td class="text-center"><?php echo $tong_ngay_nghikhongphep; ?></td>
                                        <td class="text-center">
                                            <?php 
                                            $tong_tangca = 0;
                                            foreach ($baocao_nhanvien as $nv) {
                                                $tong_tangca += $nv->ngay_tangca;
                                            }
                                            echo $tong_tangca;
                                            ?>
                                        </td>
                                        <td class="text-center"><?php echo number_format($tong_gio_lam); ?>h</td>
                                        <td class="text-right">
                                            <strong class="text-danger">
                                                <?php echo number_format($tong_luong_dukien, 0, ',', '.'); ?> đ
                                            </strong>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p>Không có dữ liệu chấm công trong tháng này</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Ghi chú -->
            <div class="card">
                <div class="card-header bg-secondary">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Ghi chú
                    </h3>
                </div>
                <div class="card-body">
                    <ul>
                        <li><strong>Lương dự kiến</strong> được tính dựa trên: Lương cơ bản / Số ngày trong tháng × Số ngày đi làm</li>
                        <li><strong>Đi trễ:</strong> Trừ 20% lương ngày đó</li>
                        <li><strong>Tăng ca:</strong> Cộng thêm 50% lương ngày đó</li>
                        <li><strong>Nghỉ không phép:</strong> Không được tính lương</li>
                        <li>Báo cáo này chỉ mang tính chất tham khảo, lương thực tế có thể khác</li>
                    </ul>
                </div>
            </div>

        </div>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<script>
// Biểu đồ Pie - Trạng thái
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: <?php echo $chartLabelsJSON; ?>,
        datasets: [{
            data: <?php echo $chartDataJSON; ?>,
            backgroundColor: <?php echo $chartColorsJSON; ?>,
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += context.parsed + ' lần';
                        return label;
                    }
                }
            }
        }
    }
});

// Biểu đồ Line - Theo ngày
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
const dailyChart = new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: <?php echo $lineLabelsJSON; ?>,
        datasets: [{
            label: 'Số lượng chấm công',
            data: <?php echo $lineDataJSON; ?>,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Xuất Excel
function exportExcel() {
    const table = document.getElementById('reportTable');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Báo cáo chấm công"});
    XLSX.writeFile(wb, 'BaoCaoChamCong_<?php echo $filter_thang; ?>_<?php echo $filter_nam; ?>.xlsx');
    alert('Xuất Excel thành công!');
}

// Xuất PDF
function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');
    
    doc.setFont('helvetica');
    doc.setFontSize(18);
    doc.text('BAO CAO CHAM CONG', 148, 15, { align: 'center' });
    
    doc.setFontSize(12);
    doc.text('Thang <?php echo $filter_thang; ?>/<?php echo $filter_nam; ?>', 148, 25, { align: 'center' });
    
    doc.setFontSize(10);
    doc.text('Ngay xuat: ' + new Date().toLocaleDateString('vi-VN'), 20, 35);
    
    const table = document.getElementById('reportTable');
    const rows = [];
    
    const headers = ['STT', 'Nhan vien', 'Chuc vu', 'Di lam', 'Di tre', 'Nghi phep', 'Nghi KP', 'Tang ca', 'Tong gio', 'Luong DK'];
    rows.push(headers);
    
    const tbody = table.querySelector('tbody');
    const trs = tbody.querySelectorAll('tr');
    trs.forEach(tr => {
        const tds = tr.querySelectorAll('td');
        if (tds.length > 1) {
            const row = [];
            for (let i = 0; i < 10; i++) {
                row.push(tds[i].innerText.trim().replace(/\n/g, ' '));
            }
            rows.push(row);
        }
    });
    
    doc.autoTable({
        head: [rows[0]],
        body: rows.slice(1),
        startY: 40,
        styles: { 
            fontSize: 8, 
            font: 'helvetica',
            cellPadding: 2
        },
        headStyles: { 
            fillColor: [0, 123, 255],
            textColor: [255, 255, 255],
            fontStyle: 'bold'
        },
        alternateRowStyles: {
            fillColor: [245, 245, 245]
        },
        margin: { top: 10, right: 10, bottom: 10, left: 10 }
    });
    
    doc.save('BaoCaoChamCong_<?php echo $filter_thang; ?>_<?php echo $filter_nam; ?>.pdf');
    alert('Xuất PDF thành công!');
}

// In báo cáo
function printReport() {
    const table = document.getElementById('reportTable');
    const printWindow = window.open('', '', 'height=600,width=800');
    
    printWindow.document.write('<html><head><title>In báo cáo chấm công</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
    printWindow.document.write('h2 { text-align: center; margin-bottom: 20px; }');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
    printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
    printWindow.document.write('th { background-color: #007bff; color: white; }');
    printWindow.document.write('tr:nth-child(even) { background-color: #f9f9f9; }');
    printWindow.document.write('@media print { body { margin: 0; padding: 0; } }');
    printWindow.document.write('</style></head><body>');
    
    printWindow.document.write('<h2>BÁAO CÁO CHẤM CÔNG - THÁNG <?php echo $filter_thang; ?>/<?php echo $filter_nam; ?></h2>');
    printWindow.document.write(table.outerHTML);
    printWindow.document.write('<p style="text-align: center; margin-top: 20px; font-size: 12px;">');
    printWindow.document.write('Ngày in: ' + new Date().toLocaleDateString('vi-VN'));
    printWindow.document.write('</p>');
    printWindow.document.write('</body></html>');
    
    printWindow.document.close();
    printWindow.print();
}
</script>