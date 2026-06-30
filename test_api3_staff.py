import requests
import pytest
URL = "http://localhost/quanlyxemay2_fixed/public/api/nhanvien"

def test_add_nhanvien_empty_data():
    payload = {"ten_nv": ""} # Thiếu dữ liệu
    response = requests.post(URL, json=payload)
    assert response.status_code == 422

def test_delete_nhanvien_with_history():
    # Giả sử ID 1 đã có lịch sử công việc
    response = requests.delete(f"{URL}/1")
    assert response.status_code == 409 # Chặn xóa