import pytest

def validate_stock_quantity(quantity_input: str) -> bool:
    if not quantity_input.isdigit():
        return False
    qty = int(quantity_input)
    if qty < 1 or qty > 100:
        return False
    return True

@pytest.mark.parametrize("quantity_input, expected", [
    ("1", True),
    ("0", False),
    ("100", True),
    ("101", False),
    ("", False)
])
def test_validate_stock_quantity(quantity_input, expected):
    assert validate_stock_quantity(quantity_input) == expected