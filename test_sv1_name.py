import pytest

def validate_bike_name(name: str) -> bool:
    if not name or len(name.strip()) == 0:
        return False
    if len(name) > 50:
        return False
    return True

@pytest.mark.parametrize("name, expected", [
    ("Honda Air Blade 160", True),
    ("", False),
    ("   ", False),
    ("X" * 50, True),
    ("X" * 51, False)
])
def test_validate_bike_name(name, expected):
    assert validate_bike_name(name) == expected