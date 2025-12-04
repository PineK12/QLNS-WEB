<?php
session_start();

// Nhúng kết nối DB
require_once './config/database.php';

// Lấy trang hiện tại từ URL
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Include header và sidebar
include './includes/header.php';
include './includes/sidebar.php';

// Routing logic - Load nội dung trang tương ứng
switch ($page) {
    case 'dashboard':
        require_once './class/Dashboard.php';
        $dashboard = new Dashboard();
        $tongNV = $dashboard->countNhanVien();
        $diLamHomNay = $dashboard->countChamCongHomNay();
        $quyLuong = $dashboard->sumQuyLuong();
        $donNghiChoDuyet = $dashboard->countDonNghiChoDuyet();
        $listNVMoi = $dashboard->getNhanVienMoi();
        $chartData = $dashboard->getChartData();

        // Chuẩn bị dữ liệu biểu đồ
        $labels = [];
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $found = false;
            foreach ($chartData as $item) {
                if ($item->ngay == $date) {
                    $labels[] = date('d/m', strtotime($date));
                    $data[] = $item->so_luong;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $labels[] = date('d/m', strtotime($date));
                $data[] = 0;
            }
        }
        $chartLabelsJSON = json_encode($labels);
        $chartDataJSON = json_encode($data);
        ?>

        <!-- Content Wrapper cho Dashboard -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Dashboard</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                                <li class="breadcrumb-item active">Dashboard</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?php echo $tongNV; ?></h3>
                                    <p>Tổng nhân viên</p>
                                </div>
                                <div class="icon"><i class="ion ion-person-add"></i></div>
                                <a href="index.php?page=nhanvien_list" class="small-box-footer">
                                    Chi tiết <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?php echo $diLamHomNay; ?></h3>
                                    <p>Đã chấm công hôm nay</p>
                                </div>
                                <div class="icon"><i class="ion ion-checkmark-circled"></i></div>
                                <a href="index.php?page=chamcong" class="small-box-footer">
                                    Chi tiết <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3 style="color: white;"><?php echo number_format($quyLuong); ?> VNĐ</h3>
                                    <p style="color: white;">Quỹ lương tháng này</p>
                                </div>
                                <div class="icon"><i class="ion ion-cash"></i></div>
                                <a href="index.php?page=bangluong" class="small-box-footer" style="color: white;">
                                    Chi tiết <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3><?php echo $donNghiChoDuyet; ?></h3>
                                    <p>Đơn nghỉ chờ duyệt</p>
                                </div>
                                <div class="icon"><i class="ion ion-document-text"></i></div>
                                <a href="index.php?page=donnhi_duyet" class="small-box-footer">
                                    Chi tiết <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header border-0">
                                    <h3 class="card-title">Thống kê chấm công 7 ngày</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="attendanceChart" style="height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Nhân viên mới nhất</h3>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="users-list clearfix">
                                        <?php if (count($listNVMoi) > 0): ?>
                                            <?php foreach ($listNVMoi as $nv): ?>
                                                <li>
                                                    <img src="https://via.placeholder.com/128" alt="User">
                                                    <a class="users-list-name" href="#"><?php echo $nv->ho . ' ' . $nv->ten; ?></a>
                                                    <span class="users-list-date">
                                                        <?php echo date('d/m/Y', strtotime($nv->ngayvaolam)); ?>
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-center">Chưa có nhân viên nào.</p>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <div class="card-footer text-center">
                                    <a href="index.php?page=nhanvien_list">Xem tất cả nhân viên</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <?php
        break;

    case 'nhanvien_list':
        // Include trang danh sách nhân viên
        include './pages/nhanvien_list.php';
        break;

    case 'nhanvien_add':
        // Include trang thêm nhân viên
        include './pages/nhanvien_add.php';
        break;

    case 'nhanvien_edit':
        // Include trang sửa nhân viên
        include './pages/nhanvien_edit.php';
        break;

    case 'nhanvien_profile':
        // Include trang hồ sơ nhân viên
        include './pages/nhanvien_profile.php';
        break;

    case 'chamcong':
        // Include trang chấm công
        include './pages/chamcong.php';
        break;

    case 'bangluong':
        // Include trang bảng lương
        include './pages/bangluong.php';
        break;

    case 'baocao_tonghop':
        // Include trang báo cáo tổng hợp
        include './pages/baocao_tonghop.php';
        break;

    case 'thongke_nhansu':
        // Include trang thống kê nhân sự
        include './pages/thongke_nhansu.php';
        break;

    case 'thongke_luong':
        // Include trang thống kê lương
        include './pages/thongke_luong.php';
        break;

    default:
        // Nếu không tìm thấy trang, hiển thị 404
        ?>
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <h1>404 - Không tìm thấy trang</h1>
                        </div>
                    </div>
                </div>
            </section>
            <section class="content">
                <div class="container-fluid">
                    <div class="alert alert-danger">
                        <h5><i class="icon fas fa-ban"></i> Lỗi!</h5>
                        Trang bạn tìm kiếm không tồn tại.
                        <a href="index.php">Quay lại Dashboard</a>
                    </div>
                </div>
            </section>
        </div>
        <?php
        break;
}

// Include footer
include './includes/footer.php';
?>