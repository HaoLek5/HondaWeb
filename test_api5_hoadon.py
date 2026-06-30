import requests
import pytest

URL = "http://localhost/quanlyxemay2_fixed/public/api/hoadon"

def test_create_hoadon_invalid_data():
    # Thiếu danh sách items
    payload = {"id_khachhang": 1, "id_nhanvien": 1, "items": []}
    response = requests.post(URL, json=payload)
    assert response.status_code == 422

def test_create_hoadon_success():
    payload = {
        "id_khachhang": 1, 
        "id_nhanvien": 1, 
        "items": [
            {
                "id_xe": 1, 
                "so_luong": 1,  # Phải thêm trường này
                "gia_ban": 50000000 # Phải thêm trường này
            }
        ]
    }
    response = requests.post(URL, json=payload)
    
    # Kiểm tra status code
    assert response.status_code == 201
    
    # Kiểm tra JSON
    data = response.json()
    assert data['status'] == 'success'