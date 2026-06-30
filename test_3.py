import pytest
import requests

URL = "http://localhost/quanlyxemay2_fixed/public/api/khachhang" # Điều chỉnh đường dẫn API cho khớp
headers = {"Accept": "application/json"}

def test_add_khachhang_success():
    """TC_KH_01: Thêm khách hàng hợp lệ thành công"""
    payload = {
        "ten_kh": "Nguyen Van Khach",
        "sdt": "0987654321",
        "dia_chi": "Ha Noi"
    }
    response = requests.post(URL, json=payload, headers=headers)
    
    assert response.status_code == 201
    data = response.json()
    assert data.get('status') == 'success'
    assert "them khach hang thanh cong" in data.get('message', '').lower()

def test_add_khachhang_empty_name():
    """TC_KH_02: Để trống tên khách hàng"""
    payload = {
        "ten_kh": "",
        "sdt": "0987654322",
        "dia_chi": "Ha Noi"
    }
    response = requests.post(URL, json=payload, headers=headers)
    
    assert response.status_code == 422
    data = response.json()
    assert data.get('status') == 'error'
    assert "vui long nhap" in data.get('message', '').lower()

def test_add_khachhang_empty_sdt():
    """TC_KH_03: Để trống số điện thoại"""
    payload = {
        "ten_kh": "Nguyen Van B",
        "sdt": "",
        "dia_chi": "Ha Noi"
    }
    response = requests.post(URL, json=payload, headers=headers)
    
    assert response.status_code == 422
    data = response.json()
    assert data.get('status') == 'error'
    assert "vui long nhap" in data.get('message', '').lower()

def test_add_khachhang_duplicate_sdt():
    """TC_KH_04: Trùng lặp số điện thoại"""
    payload = {
        "ten_kh": "Nguyen Van C",
        "sdt": "0987654333",
        "dia_chi": "Ha Noi"
    }
    
    # Gửi lần 1: Tạo mới thành công
    requests.post(URL, json=payload, headers=headers)
    
    # Gửi lần 2: Trùng lặp số điện thoại
    response = requests.post(URL, json=payload, headers=headers)
    
    assert response.status_code == 409
    data = response.json()
    assert data.get('status') == 'error'
    assert "da ton tai" in data.get('message', '').lower()