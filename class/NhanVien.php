<?php
class NhanVien {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Lấy danh sách toàn bộ nhân viên
    public function getAll() {
        // Kết nối bảng nhanvien với bảng role và trinhdo để lấy tên thay vì số ID
        $sql = "SELECT nv.*, r.ten as ten_chucvu, t.loai as ten_trinhdo 
                FROM nhanvien nv
                LEFT JOIN role r ON nv.id_role = r.id
                LEFT JOIN trinhdo t ON nv.id_trinhdo = t.id
                ORDER BY nv.id DESC"; // Người mới thêm sẽ lên đầu
        
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    // Hàm xóa nhân viên (Chuẩn bị sẵn cho bước sau)
    public function delete($id) {
        $sql = "DELETE FROM nhanvien WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Hàm thêm mới nhân viên
    public function add($data) {
        $sql = "INSERT INTO nhanvien (ho, ten, diachi, sdt, ngaysinh, id_trinhdo, id_role, luongcoban, ngayvaolam) 
                VALUES (:ho, :ten, :diachi, :sdt, :ngaysinh, :id_trinhdo, :id_role, :luongcoban, :ngayvaolam)";
        
        $this->db->query($sql);
        
        // Gán dữ liệu vào tham số
        $this->db->bind(':ho', $data['ho']);
        $this->db->bind(':ten', $data['ten']);
        $this->db->bind(':diachi', $data['diachi']);
        $this->db->bind(':sdt', $data['sdt']);
        $this->db->bind(':ngaysinh', $data['ngaysinh']);
        $this->db->bind(':id_trinhdo', $data['id_trinhdo']);
        $this->db->bind(':id_role', $data['id_role']);
        $this->db->bind(':luongcoban', $data['luongcoban']);
        $this->db->bind(':ngayvaolam', $data['ngayvaolam']);

        return $this->db->execute();
    }

    // Lấy thông tin chi tiết 1 nhân viên theo ID
    public function getById($id) {
        $sql = "SELECT nv.*, r.ten as ten_chucvu, t.loai as ten_trinhdo 
                FROM nhanvien nv
                LEFT JOIN role r ON nv.id_role = r.id
                LEFT JOIN trinhdo t ON nv.id_trinhdo = t.id
                WHERE nv.id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
}
?>