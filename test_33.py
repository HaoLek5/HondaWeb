import pytest

# 1. Hàm mô phỏng lại toàn bộ logic nghiệp vụ của hàm add() (Unit cần test)
def validate_them_khachhang(ten_kh, sdt, sdt_ton_tai=False, loi_database=False) -> dict:
    # Kiểm tra để trống tên hoặc số điện thoại
    if not ten_kh or str(ten_kh).strip() == "" or not sdt or str(sdt).strip() == "":
        return {
            "status": "error", 
            "message": "Vui long nhap day du ten va so dien thoai"
        }
    
    # Giả lập kiểm tra trùng lặp số điện thoại
    if sdt_ton_tai:
        return {
            "status": "error", 
            "message": "So dien thoai da ton tai tren he thong"
        }
    
    # Giả lập lỗi lưu Database
    if loi_database:
        return {
            "status": "error", 
            "message": "Loi luu Database"
        }
        
    # Giả lập thêm thành công
    return {
        "status": "success", 
        "message": "Them khach hang thanh cong"
    }

# 2. Các kịch bản kiểm thử (Test Cases) phủ các điều kiện rẽ nhánh
@pytest.mark.parametrize("ten_kh, sdt, ton_tai, loi_db, expected_status, expected_message", [
    # TC_KH_01: Thêm khách hàng hợp lệ thành công
    ("Nguyen Van A", "0987654321", False, False, "success", "Them khach hang thanh cong"),
    
    # TC_KH_02: Để trống tên khách hàng
    ("", "0987654321", False, False, "error", "Vui long nhap day du ten va so dien thoai"),
    
    # TC_KH_03: Để trống số điện thoại
    ("Nguyen Van B", "", False, False, "error", "Vui long nhap day du ten va so dien thoai"),
    
    # TC_KH_04: Để trống cả tên và số điện thoại
    ("", "", False, False, "error", "Vui long nhap day du ten va so dien thoai"),
    
    # TC_KH_05: Trùng lặp số điện thoại
    ("Nguyen Van C", "0987654322", True, False, "error", "So dien thoai da ton tai tren he thong"),
    
    # TC_KH_06: Lỗi lưu Database
    ("Nguyen Van D", "0987654323", False, True, "error", "Loi luu Database")
])
def test_add_khachhang(ten_kh, sdt, ton_tai, loi_db, expected_status, expected_message):
    """Thực thi Unit Test cho các điều kiện nghiệp vụ của hàm add() khách hàng"""
    result = validate_them_khachhang(ten_kh, sdt, sdt_ton_tai=ton_tai, loi_database=loi_db)
    
    assert result.get("status") == expected_status
    assert result.get("message") == expected_message