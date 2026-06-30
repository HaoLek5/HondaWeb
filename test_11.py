import pytest

# 1. Hàm mô phỏng lại toàn bộ logic nghiệp vụ của hàm add()
def validate_them_xemay(ten_xe: str, gia_ban) -> str:
    # Kiểm tra để trống tên
    if not ten_xe or ten_xe.strip() == "":
        return "Ten xe khong duoc de trong"

    # Kiểm tra giá tiền hợp lệ (phải là số và >= 0)
    try:
        gia = float(gia_ban)
        if gia < 0:
            raise ValueError
    except (ValueError, TypeError):
        return "Gia ban khong duoc de trong va phai la so lon hon hoac bang 0"

    # (Giả lập kiểm tra tồn tại)
    if ten_xe == "Honda Winner X Duplicate":
        return "San pham nay da co trong kho!"

    return "them_thanh_cong"

# 2. Các kịch bản kiểm thử (Test Cases) phủ the phân vùng tương đương & giá trị biên
@pytest.mark.parametrize("ten_xe, gia_ban, expected_message", [
    # TC_01: Hợp lệ
    ("Honda Winner 1", -500000, "them_thanh_cong"),
    
    # TC_02: Để trống tên
    ("", 45000000, "Ten xe khong duoc de trong"),
    
    # TC_03: Để trống khoảng trắng tên
    ("   ", 45000000, "Ten xe khong duoc de trong"),
    
    # TC_04: Giá bán để trống
    ("Xe Gia Trong", "", "Gia ban khong duoc de trong va phai la so lon hon hoac bang 0"),
    
    # TC_05: Giá bán không phải là số (chuỗi ký tự)
    ("Xe Gia Chuoi", "abc_xyz", "Gia ban khong duoc de trong va phai la so lon hon hoac bang 0"),
    
    # TC_06: Giá bán âm
    ("Xe Gia Am", -500000, "Gia ban khong duoc de trong va phai la so lon hon hoac bang 0"),
    
    # TC_07: Trùng lặp tên xe
    ("Honda Winner X Duplicate", 45000000, "San pham nay da co trong kho!")
])
def test_add_xemay(ten_xe, gia_ban, expected_message):
    """Thực thi Unit Test cho các điều kiện nghiệp vụ của hàm add()"""
    result = validate_them_xemay(ten_xe, gia_ban)
    assert result == expected_message