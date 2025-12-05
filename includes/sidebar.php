<?php
// Lấy trang hiện tại từ tham số URL 'page'
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
        <span class="brand-text font-weight-light">Quản Lý NV</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <a href="#" class="d-block">Admin User</a>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <li class="nav-item">
                    <a href="index.php?page=dashboard"
                        class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <?php
                $pages_nv = ['nhanvien_list', 'nhanvien_add', 'nhanvien_edit', 'nhanvien_profile'];
                $open_nv = in_array($current_page, $pages_nv);
                ?>
                <li class="nav-item <?php echo $open_nv ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo $open_nv ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Quản Lý Nhân Viên
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="index.php?page=nhanvien_list"
                                class="nav-link <?php echo ($current_page == 'nhanvien_list') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Danh sách nhân viên</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=nhanvien_add"
                                class="nav-link <?php echo ($current_page == 'nhanvien_add') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Thêm nhân viên</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <?php
                $pages_cc = ['chamcong', 'lichsu_chamcong', 'baocao_chamcong'];
                $open_cc = in_array($current_page, $pages_cc);
                ?>
                <li class="nav-item <?php echo $open_cc ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo $open_cc ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-clock"></i>
                        <p>
                            Chấm Công
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="index.php?page=chamcong"
                                class="nav-link <?php echo ($current_page == 'chamcong') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Chấm công hôm nay</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=lichsu_chamcong"
                                class="nav-link <?php echo ($current_page == 'lichsu_chamcong') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Lịch sử chấm công</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Báo cáo chấm công</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <?php
                $pages_luong = ['bangluong', 'tinhluong', 'phieuluong'];
                $open_luong = in_array($current_page, $pages_luong);
                ?>
                <li class="nav-item <?php echo $open_luong ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo $open_luong ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-money-bill-wave"></i>
                        <p>
                            Quản Lý Lương
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="index.php?page=bangluong"
                                class="nav-link <?php echo ($current_page == 'bangluong') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Bảng lương</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Tính lương tháng</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Phiếu lương</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-header">HỆ THỐNG</li>

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-user-tag"></i>
                        <p>Vai trò & Quyền</p>
                    </a>
                </li>

                <?php
                $pages_baocao = ['baocao_tonghop', 'thongke_nhansu', 'thongke_luong'];
                $open_baocao = in_array($current_page, $pages_baocao);
                ?>
                <li class="nav-item <?php echo $open_baocao ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo $open_baocao ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>
                            Báo Cáo & Thống Kê
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="index.php?page=baocao_tonghop"
                                class="nav-link <?php echo ($current_page == 'baocao_tonghop') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Báo cáo tổng hợp</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=thongke_nhansu"
                                class="nav-link <?php echo ($current_page == 'thongke_nhansu') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Thống kê nhân sự</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-header">CÀI ĐẶT</li>

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>Cài đặt hệ thống</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>