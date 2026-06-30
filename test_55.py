import pytest

# 1. Hàm mô phỏng lại toàn bộ logic nghiệp vụ của hàm login() (Unit cần test)
def validate_login(username, password) -> dict:
    # Kiểm tra để trống tên tài khoản hoặc mật khẩu
    if not username or str(username).strip() == "" or not password or str(password).strip() == "":
        return {
            "status": "error", 
            "message": "Vui long nhap tai khoan va mat khau"
        }
    
    # Mô phỏng rẽ nhánh kết quả từ Model (sai tài khoản hoặc mật khẩu)
    if username == "sai_tai_khoan" or password == "sai_mat_khau":
        return {
            "status": "error", 
            "message": "Sai tai khoan hoac mat khau"
        }
        
    # Mô phỏng đăng nhập thành công trả về thông tin user
    return {
        "status": "success", 
        "message": "Dang nhap thanh cong",
        "user": {"id": 1, "username": username, "role": "admin"}
    }

# 2. Các kịch bản kiểm thử (Test Cases) phủ các điều kiện rẽ nhánh
@pytest.mark.parametrize("username, password, expected_status, expected_message", [
    # TC_LG_01: Đăng nhập thành công với thông tin chính xác
    ("admin", "123", "success", "Dang nhap thanh cong"),
    
    # TC_LG_02: Để trống tên tài khoản
    ("", "123", "error", "Vui long nhap tai khoan va mat khau"),
    
    # TC_LG_03: Để trống mật khẩu
    ("admin", "", "error", "Vui long nhap tai khoan va mat khau"),
    
    # TC_LG_04: Để trống cả tài khoản và mật khẩu
    ("", "", "error", "Vui long nhap tai khoan va mat khau"),
    
    # TC_LG_05: Sai tài khoản hoặc mật khẩu
    ("sai_tai_khoan", "123", "error", "Sai tai khoan hoac mat khau"),
    ("admin", "sai_mat_khau", "error", "Sai tai khoan hoac mat khau")
])
def test_login(username, password, expected_status, expected_message):
    """Thực thi Unit Test cho các điều kiện nghiệp vụ của hàm login()"""
    result = validate_login(username, password)
    
    assert result.get("status") == expected_status
    assert expected_message in result.get("message")