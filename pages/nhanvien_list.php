<?php
// pages/nhanvien_list.php
// CHỈ CẦN LOGIC XỬ LÝ DỮ LIỆU CỦA RIÊNG TRANG NÀY
require_once './class/NhanVien.php';
$nvModel = new NhanVien();
$listNV = $nvModel->getAll();

// Lấy danh sách chức vụ và trình độ để đổ vào Select Box trong Modal
// (Lưu ý: Bạn cần thêm 2 hàm này vào class NhanVien hoặc tạo model riêng, tạm thời mình giả lập dữ liệu tĩnh hoặc query nhanh)
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
                    <h1>Danh sách nhân viên</h1>
                </div>
                <div class="col-sm-6">
                    <a href="index.php?page=nhanvien_add" class="btn btn-primary float-sm-right">
                        <i class="fas fa-plus"></i> Thêm nhân viên
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Dữ liệu nhân sự</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th style="width: 20%">Họ tên</th>
                                <th>Chức vụ</th>
                                <th>Trình độ</th>
                                <th>Ngày vào làm</th>
                                <th style="width: 20%" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($listNV) > 0): ?>
                                <?php foreach ($listNV as $nv): ?>
                                    <tr>
                                        <td>#<?php echo $nv->id; ?></td>
                                        <td>
                                            <a href="index.php?page=nhanvien_profile&id=<?php echo $nv->id; ?>"
                                                style="font-weight: bold; color: #007bff;">
                                                <?php echo $nv->ho . ' ' . $nv->ten; ?>
                                            </a>
                                            <br />
                                            <small>SĐT: <?php echo $nv->sdt; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-success"><?php echo $nv->ten_chucvu; ?></span>
                                        </td>
                                        <td><?php echo $nv->ten_trinhdo; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($nv->ngayvaolam)); ?></td>
                                        <td class="project-actions text-center">
                                            <button type="button" class="btn btn-info btn-sm btn-edit" data-toggle="modal"
                                                data-target="#modalEdit" data-id="<?php echo $nv->id; ?>"
                                                data-ho="<?php echo $nv->ho; ?>" data-ten="<?php echo $nv->ten; ?>"
                                                data-sdt="<?php echo $nv->sdt; ?>" data-diachi="<?php echo $nv->diachi; ?>"
                                                data-ngaysinh="<?php echo $nv->ngaysinh; ?>"
                                                data-role="<?php echo $nv->id_role; ?>"
                                                data-trinhdo="<?php echo $nv->id_trinhdo; ?>"
                                                data-luong="<?php echo $nv->luongcoban; ?>"
                                                data-ngayvaolam="<?php echo $nv->ngayvaolam; ?>">
                                                <i class="fas fa-pencil-alt"></i> Sửa
                                            </button>

                                            <a class="btn btn-danger btn-sm"
                                                href="modules/nhanvien/delete.php?id=<?php echo $nv->id; ?>"
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa nhân viên <?php echo $nv->ho . ' ' . $nv->ten; ?>?');">
                                                <i class="fas fa-trash"></i> Xóa
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Chưa có dữ liệu.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="modules/nhanvien/edit.php" method="POST">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="modalEditLabel">Cập nhật thông tin nhân viên</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Họ</label>
                                <input type="text" class="form-control" name="ho" id="edit_ho" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tên</label>
                                <input type="text" class="form-control" name="ten" id="edit_ten" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ngày sinh</label>
                                <input type="date" class="form-control" name="ngaysinh" id="edit_ngaysinh" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Số điện thoại</label>
                                <input type="text" class="form-control" name="sdt" id="edit_sdt" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <input type="text" class="form-control" name="diachi" id="edit_diachi">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Chức vụ</label>
                                <select class="form-control" name="id_role" id="edit_role">
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?php echo $r->id; ?>"><?php echo $r->ten; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Trình độ</label>
                                <select class="form-control" name="id_trinhdo" id="edit_trinhdo">
                                    <?php foreach ($trinhdos as $t): ?>
                                        <option value="<?php echo $t->id; ?>"><?php echo $t->loai; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lương cơ bản</label>
                                <input type="number" class="form-control" name="luongcoban" id="edit_luong">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ngày vào làm</label>
                                <input type="date" class="form-control" name="ngayvaolam" id="edit_ngayvaolam">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-info">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Khi trang đã tải xong
    document.addEventListener("DOMContentLoaded", function () {
        // Bắt sự kiện click vào nút Sửa có class .btn-edit
        $('.btn-edit').on('click', function () {
            // Lấy dữ liệu từ data-attribute của nút đó
            var id = $(this).data('id');
            var ho = $(this).data('ho');
            var ten = $(this).data('ten');
            var sdt = $(this).data('sdt');
            var diachi = $(this).data('diachi');
            var ngaysinh = $(this).data('ngaysinh');
            var role = $(this).data('role');
            var trinhdo = $(this).data('trinhdo');
            var luong = $(this).data('luong');
            var ngayvaolam = $(this).data('ngayvaolam');

            // Gán dữ liệu vào các ô input trong Modal
            $('#edit_id').val(id);
            $('#edit_ho').val(ho);
            $('#edit_ten').val(ten);
            $('#edit_sdt').val(sdt);
            $('#edit_diachi').val(diachi);
            $('#edit_ngaysinh').val(ngaysinh);
            $('#edit_role').val(role);     // Tự động chọn đúng option
            $('#edit_trinhdo').val(trinhdo); // Tự động chọn đúng option
            $('#edit_luong').val(luong);
            $('#edit_ngayvaolam').val(ngayvaolam);
        });
    });
</script>