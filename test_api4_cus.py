import requests
import pytest
URL = "http://localhost/quanlyxemay2_fixed/public/api/khachhang"

def test_add_khachhang_success():
    payload = {"ten_kh": "Nguyen Van B", "sdt": "0368847521"}
    response = requests.post(URL, json=payload)
    assert response.status_code == 201

def test_add_khachhang_duplicate_sdt():
    payload = {"ten_kh": "User B", "sdt": "0912345678"}
    requests.post(URL, json=payload)
    response = requests.post(URL, json=payload)
    assert response.status_code == 409 # SĐT đã tồn tại