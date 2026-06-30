import pytest

def validate_category(category: str) -> bool:
    valid_categories = ["xe số", "xe ga", "xe côn tay"]
    return category.strip().lower() in valid_categories

@pytest.mark.parametrize("category, expected", [
    ("Xe ga", True),
    ("Xe côn tay", True),
    ("xE sỐ", True),
    ("Xe điện", False)
])
def test_validate_category(category, expected):
    assert validate_category(category) == expected