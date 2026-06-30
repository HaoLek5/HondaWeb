import pytest

# 1. Hàm mô phỏng lại logic nghiệp vụ của hàm add() (Unit cần test)
def validate_them_nhanvien(ten_nv, sdt) -> dict:
    # Kiểm tra để trống tên hoặc số điện thoại
    if not ten_nv or str(ten_nv).strip() == "" or not sdt or str(sdt).strip() == "":
        return {
            "status": "error", 
            "message": "Vui long nhap ten va so dien thoai"
        }
    
    # Giả lập việc gọi model->create($data) thành công/thất bại
    # Ở đây ta có thể tách hoặc giả lập kết quả trả về true/false ngầm định
    # Giả sử truyền tên đặc biệt để mô phỏng lỗi database (hoặc cứ cho là thành công nếu đủ đk)
    if ten_nv == "Loi Database":
        return {"status": "error", "message": "Khong the them nhan vien"}
        
    return {"status": "success", "message": "Them nhan vien thanh cong"}

# 2. Các kịch bản kiểm thử (Test Cases)
@pytest.mark.parametrize("ten_nv, sdt, expected_status, expected_message", [
    # TC_NV_01: Hợp lệ - Thêm thành công
    ("Nguyen Van A", "0912345678", "success", "Them nhan vien thanh cong"),
    
    # TC_NV_02: Để trống tên nhân viên
    ("Lan Anh", "0912345678", "error", "Vui long nhap ten va so dien thoai"),
    
    # TC_NV_03: Để trống số điện thoại
    ("Nguyen Van B", "", "error", "Vui long nhap ten va so dien thoai"),
    
    # TC_NV_04: Để trống cả tên và số điện thoại
    ("", "", "error", "Vui long nhap ten va so dien thoai"),
    
    # TC_NV_05: Mô phỏng lỗi lưu Database
    ("Loi Database", "0912345678", "error", "Khong the them nhan vien")
])
def test_add_nhanvien(ten_nv, sdt, expected_status, expected_message):
    """Thực thi Unit Test cho các điều kiện của hàm add() nhân viên"""
    result = validate_them_nhanvien(ten_nv, sdt)
    
    assert result.get("status") == expected_status
    assert result.get("message") == expected_message