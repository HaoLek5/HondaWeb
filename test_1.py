import pytest
import requests

URL = "http://localhost/quanlyxemay2_fixed/public/api/xemay"
headers = {"Accept": "application/json"}

def test_add_xemay_success():
    """TC_ADD_01: Thêm xe hợp lệ thành công với các giá trị mặc định"""
    payload = {
        "ten_xe": "Honda Winner 1 Test",
        "gia_ban": 45000000,
        "hinh_anh": "", 
        "id_phanh": 1,           
        "id_dong_co": 1,          
        "id_mau": 1,             
        "id_loai_xe": 1,         
        "trang_thai": 1           
    }
    response = requests.post(URL, json=payload, headers=headers)
    
    assert response.status_code in [200, 201]
    data = response.json()
    assert data.get('status') == 'success'
    assert "thanh cong" in data.get('message', '').lower()

def test_add_xemay_empty_name():
    """TC_ADD_02: Bỏ trống tên xe (Dữ liệu không hợp lệ)"""
    payload = {
        "ten_xe": "",
        "gia_ban": 45000000,
        "hinh_anh": "",
        "id_phanh": 1,
        "id_dong_co": 1,
        "id_mau": 1,
        "id_loai_xe": 1,
        "trang_thai": 1
    }
    response = requests.post(URL, json=payload, headers=headers)
    
    assert response.status_code == 422
    data = response.json()
    assert data.get('status') == 'error'
    assert "khong duoc de trong" in data.get('message', '').lower()

def test_add_xemay_duplicate():
    """TC_ADD_03: Tên xe bị trùng lặp"""
    ten_xe_trung = "Honda Winner X Duplicate"
    payload = {
        "ten_xe": ten_xe_trung,
        "gia_ban": 45000000,
        "hinh_anh": "",
        "id_phanh": 1,
        "id_dong_co": 1,
        "id_mau": 1,
        "id_loai_xe": 1,
        "trang_thai": 1
    }

def test_add_xemay_empty_price():
    """TC_ADD_04: Giá tiền để trống"""
    payload = {
        "ten_xe": "Xe Gia Trong", "hinh_anh": "",
        "id_phanh": 1, "id_dong_co": 1, "id_mau": 1, "id_loai_xe": 1, "trang_thai": 1
        
    }
    response = requests.post(URL, json=payload, headers=headers)
    assert response.status_code == 422
    assert response.json().get('status') == 'error'

def test_add_xemay_invalid_price_format():
    """TC_ADD_05: Giá tiền không phải là số (chuỗi ký tự)"""
    payload = {
        "ten_xe": "Xe Gia Chuoi", "gia_ban": "abc_xyz", "hinh_anh": "",
        "id_phanh": 1, "id_dong_co": 1, "id_mau": 1, "id_loai_xe": 1, "trang_thai": 1
    }
    response = requests.post(URL, json=payload, headers=headers)
    assert response.status_code == 422
    data = response.json()
    assert data.get('status') == 'error'
    assert "gia ban" in data.get('message', '').lower()