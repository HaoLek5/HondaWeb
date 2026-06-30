import pytest
import requests

URL = "http://localhost/quanlyxemay2_fixed/public/api/user"

def test_login_success():
    payload = {"_action": "login", "username": "admin", "password": "123456"}
    response = requests.post(URL, json=payload)
    assert response.status_code == 200

def test_login_wrong_password():
    payload = {"_action": "login", "username": "admin", "password": "wrong"}
    response = requests.post(URL, json=payload)
    assert response.status_code == 401

def test_login_nonexistent_user():
    payload = {"_action": "login", "username": "", "password": "123456"}
    response = requests.post(URL, json=payload)
    assert response.status_code == 422