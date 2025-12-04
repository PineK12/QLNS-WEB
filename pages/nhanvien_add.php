<?php
// pages/nhanvien_add.php

// Lấy danh sách Chức vụ & Trình độ để hiển thị Select Box
$db = new Database();
$db->query("SELECT * FROM role");
$roles = $db->resultSet();

$db->query("SELECT * FROM trinhdo");
$trinhdos = $db->resultSet();
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Thêm nhân viên mới</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=nhanvien_list">Danh sách</a></li>
                        <li class="breadcrumb-item active">Thêm mới</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Điền thông tin nhân viên</h3>
                </div>

                <form action="modules/nhanvien/add.php" method="POST">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Họ & Tên đệm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="ho" placeholder="Ví dụ: Nguyễn Văn"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label>Ngày sinh <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="ngaysinh" required>
                                </div>
                                <div class="form-group">
                                    <label>Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="sdt" required>
                                </div>
                                <div class="form-group">
                                    <label>Chức vụ</label>
                                    <select class="form-control" name="id_role">
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role->id; ?>"><?php echo $role->ten; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="ten" placeholder="Ví dụ: An" required>
                                </div>
                                <div class="form-group">
                                    <label>Địa chỉ</label>
                                    <input type="text" class="form-control" name="diachi"
                                        placeholder="Số nhà, đường...">
                                </div>
                                <div class="form-group">
                                    <label>Lương cơ bản (VNĐ)</label>
                                    <input type="number" class="form-control" name="luongcoban" value="5000000">
                                </div>
                                <div class="form-group">
                                    <label>Trình độ</label>
                                    <select class="form-control" name="id_trinhdo">
                                        <?php foreach ($trinhdos as $td): ?>
                                            <option value="<?php echo $td->id; ?>"><?php echo $td->loai; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Ngày vào làm chính thức</label>
                            <input type="date" class="form-control" name="ngayvaolam"
                                value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Lưu nhân viên</button>
                        <a href="index.php?page=nhanvien_list" class="btn btn-default float-right">Hủy bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>