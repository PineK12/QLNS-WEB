<?php
// pages/nhanvien_profile.php

require_once './class/NhanVien.php';

// Kiểm tra có ID trên URL không
if (!isset($_GET['id'])) {
    echo "<div class='content-wrapper p-4'><div class='alert alert-danger'>Không tìm thấy mã nhân viên!</div></div>";
    return;
}

$id = $_GET['id'];
$nvModel = new NhanVien();
$nv = $nvModel->getById($id);

// Nếu không tìm thấy nhân viên trong DB
if (!$nv) {
    echo "<div class='content-wrapper p-4'><div class='alert alert-warning'>Nhân viên này không tồn tại!</div></div>";
    return;
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Hồ sơ nhân viên</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=nhanvien_list">Danh sách</a></li>
                        <li class="breadcrumb-item active">Hồ sơ</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <div class="col-md-3">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle" src="https://via.placeholder.com/128"
                                    alt="User profile picture">
                            </div>

                            <h3 class="profile-username text-center"><?php echo $nv->ho . ' ' . $nv->ten; ?></h3>

                            <p class="text-muted text-center"><?php echo $nv->ten_chucvu; ?></p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Mã nhân viên</b> <a class="float-right">#<?php echo $nv->id; ?></a>
                                </li>
                                <li class="list-group-item">
                                    <b>Ngày vào làm</b> <a
                                        class="float-right"><?php echo date('d/m/Y', strtotime($nv->ngayvaolam)); ?></a>
                                </li>
                                <li class="list-group-item">
                                    <b>Trình độ</b> <a class="float-right"><?php echo $nv->ten_trinhdo; ?></a>
                                </li>
                            </ul>

                            <a href="#" class="btn btn-primary btn-block"><b>Gửi tin nhắn</b></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#info" data-toggle="tab">Thông tin
                                        cá nhân</a></li>
                                <li class="nav-item"><a class="nav-link" href="#luong" data-toggle="tab">Lương & Phúc
                                        lợi</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">

                                <div class="active tab-pane" id="info">
                                    <form class="form-horizontal">
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Họ và tên</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control"
                                                    value="<?php echo $nv->ho . ' ' . $nv->ten; ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Ngày sinh</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control"
                                                    value="<?php echo date('d/m/Y', strtotime($nv->ngaysinh)); ?>"
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Số điện thoại</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" value="<?php echo $nv->sdt; ?>"
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Địa chỉ</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control"
                                                    value="<?php echo $nv->diachi; ?>" readonly>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" id="luong">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-center text-muted">Lương cơ bản hiện
                                                        tại</span>
                                                    <span class="info-box-number text-center text-muted mb-0">
                                                        <?php echo number_format($nv->luongcoban); ?> VNĐ
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-muted">Chưa có lịch sử tăng lương.</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>