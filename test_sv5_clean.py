import pytest

def clean_input_data(text: str) -> str:
    if text is None:
        return ""
    return text.strip()

def test_clean_input_data():
    assert clean_input_data("  Honda Vision  ") == "Honda Vision"
    assert clean_input_data(" Honda Wave") == "Honda Wave"
    assert clean_input_data("Honda SH") == "Honda SH"