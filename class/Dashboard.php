<?php
// class/Dashboard.php
class Dashboard
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // 1. Đếm tổng số nhân viên
    public function countNhanVien()
    {
        $this->db->query("SELECT COUNT(*) as total FROM nhanvien");
        $row = $this->db->single();
        return $row->total;
    }

    // 2. Đếm số người đã chấm công hôm nay (Trạng thái 'DiLam' hoặc 'DiTre')
    public function countChamCongHomNay()
    {
        $today = date('Y-m-d');
        // Đếm các dòng chấm công của ngày hôm nay có trạng thái đi làm/đi trễ
        $this->db->query("SELECT COUNT(DISTINCT id_nv) as total FROM chamcong 
                          WHERE ngay = :today AND (trangthai = 'DiLam' OR trangthai = 'DiTre')");
        $this->db->bind(':today', $today);
        $row = $this->db->single();
        return $row->total;
    }

    // 3. Tính tổng quỹ lương cơ bản (Ước tính chi phí tháng này)
    public function sumQuyLuong()
    {
        $this->db->query("SELECT SUM(luongcoban) as total FROM nhanvien");
        $row = $this->db->single();
        // Nếu chưa có ai thì trả về 0
        return $row->total ? $row->total : 0;
    }

    // 4. Đếm đơn nghỉ phép đang chờ duyệt
    public function countDonNghiChoDuyet()
    {
        $this->db->query("SELECT COUNT(*) as total FROM donnghi WHERE trangthai = 'ChoDuyet'");
        $row = $this->db->single();
        return $row->total;
    }

    // 5. Lấy danh sách 4 nhân viên mới nhất
    public function getNhanVienMoi($limit = 4)
    {
        $this->db->query("SELECT ho, ten, ngaysinh, ngayvaolam FROM nhanvien ORDER BY ngayvaolam DESC LIMIT :limit");
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    // 6. Lấy dữ liệu chấm công 7 ngày gần nhất cho biểu đồ
    public function getChartData() {
        // Lấy 7 ngày gần nhất
        $sql = "SELECT ngay, COUNT(DISTINCT id_nv) as so_luong 
                FROM chamcong 
                WHERE ngay >= DATE(NOW()) - INTERVAL 6 DAY 
                AND (trangthai = 'DiLam' OR trangthai = 'DiTre')
                GROUP BY ngay 
                ORDER BY ngay ASC";
        
        $this->db->query($sql);
        return $this->db->resultSet();
    }
}
?>