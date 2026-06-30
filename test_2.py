import pytest
import requests

URL = "http://localhost/quanlyxemay2_fixed/public/api/nhanvien"
headers = {"Accept": "application/json"}

def test_add_nhanvien_success():
    """TC_NV_01: Thêm nhân viên hợp lệ thành công"""
    payload = {
        "ten_nv": "Nguyen Van A",
        "sdt": "0912345678",
        "dia_chi": "Ha Noi"
    }
    response = requests.post(URL, json=payload, headers=headers)
    
    assert response.status_code == 201
    data = response.json()
    assert data.get('status') == 'success'
    assert "them nhan vien thanh cong" in data.get('message', '').lower()

def test_add_nhanvien_empty_name():
    """TC_NV_02: Để trống tên nhân viên"""
    payload = {
        "ten_nv": "",
        "sdt": "0912345678",
        "dia_chi": "Ha Noi"
    }
    response = requests.post(URL, json=payload, headers=headers)
    
    assert response.status_code == 422
    data = response.json()
    assert data.get('status') == 'error'
    assert "nhap ten" in data.get('message', '').lower()

def test_add_nhanvien_empty_sdt():
    """TC_NV_03: Để trống số điện thoại"""
    payload = {
        "ten_nv": "Nguyen Van B",
        "sdt": "",
        "dia_chi": "Ha Noi"
    }
    response = requests.post(URL, json=payload, headers=headers)
    
    assert response.status_code == 422
    data = response.json()
    assert data.get('status') == 'error'
    assert "so dien thoai" in data.get('message', '').lower()

def test_add_nhanvien_empty_both():
    """TC_NV_04: Để trống cả tên và số điện thoại"""
    payload = {
        "ten_nv": "",
        "sdt": "",
        "dia_chi": "Ha Noi"
    }
    response = requests.post(URL, json=payload, headers=headers)
    
    assert response.status_code == 422
    data = response.json()
    assert data.get('status') == 'error'
    assert "vui long nhap" in data.get('message', '').lower()