import pytest
import requests

URL = "http://localhost/quanlyxemay2_fixed/public/api/user"
headers = {"Accept": "application/json"}

def test_login_success():
    """TC_LG_01: Đăng nhập thành công với thông tin đúng"""
    payload = {
        "username": "admin", # Thay thế bằng username hợp lệ trong DB của bạn
        "password": "123456"    # Thay thế bằng password tương ứng
    }
    response = requests.post(URL, json=payload, headers=headers)
    
    # Thông thường đăng nhập thành công trả về 200
    assert response.status_code == 200
    data = response.json()
    assert data.get('status') == 'success'

def test_login_empty_fields():
    """TC_LG_02: Để trống tài khoản hoặc mật khẩu"""
    payload = {
        "username": "",
        "password": ""
    }
    response = requests.post(URL, json=payload, headers=headers)
    
    assert response.status_code == 422
    data = response.json()
    assert data.get('status') == 'error'
    assert "vui long nhap" in data.get('message', '').lower()

def test_login_wrong_credentials():
    """TC_LG_03: Sai tài khoản hoặc mật khẩu"""
    payload = {
        "username": "sai_tai_khoan_nay",
        "password": "sai_mat_khau_nay"
    }
    response = requests.post(URL, json=payload, headers=headers)
    
    assert response.status_code == 401
    data = response.json()
    assert data.get('status') == 'error'
    assert "sai tai khoan" in data.get('message', '').lower()