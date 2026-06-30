import pytest

# 1. Hàm mô phỏng lại toàn bộ logic nghiệp vụ của hàm add() phiếu nhập (Unit cần test)
def validate_nhapkho(data, da_dang_nhap=True, ket_qua_model="success") -> dict:
    # Kiểm tra dữ liệu đầu vào có tồn tại, có id_ncc và items không
    if not data or not data.get('id_ncc') or not data.get('items'):
        return {
            "status": "error", 
            "message": "Du lieu khong day du"
        }
    
    # Kiểm tra trạng thái đăng nhập (mô phỏng SESSION)
    if not da_dang_nhap:
        return {
            "status": "error", 
            "message": "Chua dang nhap. Vui long dang nhap lai!"
        }
        
    # Mô phỏng duyệt qua các item kiểm tra số lượng hợp lệ (như trong Model)
    for item in data.get('items', []):
        if item.get('so_luong', 0) <= 0:
            return {
                "status": "error", 
                "message": "Lỗi Model: Số lượng xe không hợp lệ."
            }
            
    # Mô phỏng kết quả trả về từ Model khi lưu
    if ket_qua_model == "error":
        return {
            "status": "error", 
            "message": "Lỗi Model: Lỗi lưu Database"
        }
        
    return {
        "status": "success", 
        "message": "Nhập kho thành công!"
    }

# 2. Các kịch bản kiểm thử (Test Cases) phủ các điều kiện rẽ nhánh
@pytest.mark.parametrize("data, dang_nhap, model_status, expected_status, expected_message", [
    # TC_PN_01: Nhập kho hợp lệ thành công
    (
        {"id_ncc": 1, "items": [{"id_xe": 1, "so_luong": 10, "gia_nhap": 35000000}]},
        True, "success", "success", "Nhập kho thành công!"
    ),
    
    # TC_PN_02: Thiếu dữ liệu đầu vào (bỏ trống id_ncc)
    (
        {"items": [{"id_xe": 1, "so_luong": 10, "gia_nhap": 35000000}]},
        True, "success", "error", "Du lieu khong day du"
    ),
    
    # TC_PN_03: Chưa đăng nhập (Session nhân viên trống)
    (
        {"id_ncc": 1, "items": [{"id_xe": 1, "so_luong": 10, "gia_nhap": 35000000}]},
        False, "success", "error", "Chua dang nhap. Vui long dang nhap lai!"
    ),
    
    # TC_PN_04: Số lượng sản phẩm nhập kho <= 0
    (
        {"id_ncc": 1, "items": [{"id_xe": 1, "so_luong": -2, "gia_nhap": 35000000}]},
        True, "success", "error", "Lỗi Model: Số lượng xe không hợp lệ."
    ),
    
    # TC_PN_05: Lỗi lưu Database ở tầng Model
    (
        {"id_ncc": 1, "items": [{"id_xe": 1, "so_luong": 5, "gia_nhap": 35000000}]},
        True, "error", "error", "Lỗi Model: Lỗi lưu Database"
    )
])
def test_add_phieunhap(data, dang_nhap, model_status, expected_status, expected_message):
    """Thực thi Unit Test cho các điều kiện nghiệp vụ của hàm add() nhập kho"""
    result = validate_nhapkho(data, da_dang_nhap=dang_nhap, ket_qua_model=model_status)
    
    assert result.get("status") == expected_status
    assert result.get("message") == expected_message