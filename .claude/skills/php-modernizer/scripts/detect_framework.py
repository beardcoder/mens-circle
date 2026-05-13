#!/usr/bin/env python3
"""
Detects installed PHP frameworks by parsing composer.json.
Returns framework information as JSON.
"""

import json
import sys
from pathlib import Path
from typing import Dict, List, Optional


def find_composer_json(start_path: str = ".") -> Optional[Path]:
    """Find composer.json in current directory or parent directories."""
    current = Path(start_path).resolve()
    
    # Check current directory first
    if (current / "composer.json").exists():
        return current / "composer.json"
    
    # Check parent directories
    for parent in current.parents:
        composer_file = parent / "composer.json"
        if composer_file.exists():
            return composer_file
    
    return None


def detect_frameworks(composer_data: dict) -> Dict[str, any]:
    """Detect frameworks from composer.json dependencies."""
    frameworks = {
        "laravel": False,
        "typo3": False,
        "symfony": False,
        "detected": [],
        "versions": {}
    }
    
    # Check both require and require-dev
    all_packages = {}
    all_packages.update(composer_data.get("require", {}))
    all_packages.update(composer_data.get("require-dev", {}))
    
    # Laravel detection
    if "laravel/framework" in all_packages:
        frameworks["laravel"] = True
        frameworks["detected"].append("Laravel")
        frameworks["versions"]["laravel"] = all_packages["laravel/framework"]
    
    # TYPO3 detection (check for any typo3/cms-* package)
    typo3_packages = [pkg for pkg in all_packages if pkg.startswith("typo3/cms-")]
    if typo3_packages:
        frameworks["typo3"] = True
        frameworks["detected"].append("TYPO3")
        # Get version from first cms package found
        frameworks["versions"]["typo3"] = all_packages[typo3_packages[0]]
    
    # Symfony detection (check for symfony framework bundle or multiple symfony components)
    symfony_packages = [pkg for pkg in all_packages if pkg.startswith("symfony/")]
    if "symfony/framework-bundle" in all_packages:
        frameworks["symfony"] = True
        frameworks["detected"].append("Symfony")
        frameworks["versions"]["symfony"] = all_packages["symfony/framework-bundle"]
    elif len(symfony_packages) >= 3:  # If multiple symfony packages, likely a Symfony project
        frameworks["symfony"] = True
        frameworks["detected"].append("Symfony")
        frameworks["versions"]["symfony"] = "components"
    
    return frameworks


def main():
    """Main execution function."""
    # Accept optional path argument
    search_path = sys.argv[1] if len(sys.argv) > 1 else "."
    
    # Find composer.json
    composer_path = find_composer_json(search_path)
    
    if not composer_path:
        print(json.dumps({
            "error": "composer.json not found",
            "frameworks": {
                "laravel": False,
                "typo3": False,
                "symfony": False,
                "detected": []
            }
        }), file=sys.stderr)
        sys.exit(1)
    
    # Parse composer.json
    try:
        with open(composer_path, 'r', encoding='utf-8') as f:
            composer_data = json.load(f)
    except json.JSONDecodeError as e:
        print(json.dumps({
            "error": f"Invalid JSON in composer.json: {e}",
            "frameworks": {
                "laravel": False,
                "typo3": False,
                "symfony": False,
                "detected": []
            }
        }), file=sys.stderr)
        sys.exit(1)
    except Exception as e:
        print(json.dumps({
            "error": f"Error reading composer.json: {e}",
            "frameworks": {
                "laravel": False,
                "typo3": False,
                "symfony": False,
                "detected": []
            }
        }), file=sys.stderr)
        sys.exit(1)
    
    # Detect frameworks
    frameworks = detect_frameworks(composer_data)
    
    # Output result as JSON
    result = {
        "composer_path": str(composer_path),
        "project_name": composer_data.get("name", "unknown"),
        "frameworks": frameworks
    }
    
    print(json.dumps(result, indent=2))
    return 0


if __name__ == "__main__":
    sys.exit(main())
