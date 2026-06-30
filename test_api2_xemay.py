import requests
import pytest
URL = "http://localhost/quanlyxemay2_fixed/public/api/xemay"

def test_add_xemay_success():
    payload = {"ten_xe": "Honda Winner X"}
    response = requests.post(URL, json=payload)
    assert response.status_code == 200

def test_add_xemay_duplicate():
    payload = {"ten_xe": "Honda Winner Y"}
    requests.post(URL, json=payload) # Thêm lần 1
    response = requests.post(URL, json=payload) # Thêm lần 2
    assert response.status_code == 200 # Trùng lặp tên xe