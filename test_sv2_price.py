import pytest

def validate_bike_price(price_input: str) -> bool:
    if not price_input.isdigit(): # Phải hoàn toàn là ký số
        return False
    price = int(price_input)
    if price <= 0:
        return False
    return True

@pytest.mark.parametrize("price_input, expected", [
    ("35000000", True),
    ("0", False),
    ("-15000000", False),
    ("35tr", False)
])
def test_validate_bike_price(price_input, expected):
    assert validate_bike_price(price_input) == expected