#!/usr/bin/env python3
"""
Scans for PHP files in a project, preferring git-tracked files.
Falls back to recursive scanning with common exclusions if no git repository.
"""

import json
import subprocess
import sys
from pathlib import Path
from typing import List, Set


# Directories to exclude when not using git
EXCLUDE_DIRS = {
    'vendor',
    'storage',
    'var',
    'cache',
    'tmp',
    'node_modules',
    'public',
    '.git',
    '.idea',
    '.vscode',
    'temp',
    'logs',
    'build',
    'dist',
}


def is_git_repository(path: Path) -> bool:
    """Check if the path is inside a git repository."""
    try:
        result = subprocess.run(
            ['git', 'rev-parse', '--git-dir'],
            cwd=path,
            capture_output=True,
            text=True,
            check=False
        )
        return result.returncode == 0
    except FileNotFoundError:
        return False


def get_git_tracked_files(path: Path) -> List[str]:
    """Get list of git-tracked PHP files."""
    try:
        result = subprocess.run(
            ['git', 'ls-files', '*.php'],
            cwd=path,
            capture_output=True,
            text=True,
            check=True
        )
        files = result.stdout.strip().split('\n')
        return [f for f in files if f]  # Filter empty strings
    except (subprocess.CalledProcessError, FileNotFoundError):
        return []


def should_exclude_dir(dir_path: Path) -> bool:
    """Check if directory should be excluded from scanning."""
    dir_name = dir_path.name
    return dir_name in EXCLUDE_DIRS or dir_name.startswith('.')


def scan_php_files_recursive(path: Path) -> List[str]:
    """Recursively scan for PHP files, excluding common directories."""
    php_files = []
    
    for item in path.rglob('*.php'):
        # Check if any parent directory should be excluded
        if any(should_exclude_dir(parent) for parent in item.parents):
            continue
        
        # Make path relative to starting path
        try:
            relative_path = item.relative_to(path)
            php_files.append(str(relative_path))
        except ValueError:
            continue
    
    return sorted(php_files)


def scan_files(start_path: str = ".") -> dict:
    """Scan for PHP files using git or recursive fallback."""
    path = Path(start_path).resolve()
    
    if not path.exists():
        return {
            "error": f"Path does not exist: {start_path}",
            "files": [],
            "method": "none"
        }
    
    # Try git first
    if is_git_repository(path):
        files = get_git_tracked_files(path)
        if files:
            return {
                "path": str(path),
                "method": "git",
                "files": files,
                "count": len(files)
            }
    
    # Fallback to recursive scanning
    files = scan_php_files_recursive(path)
    return {
        "path": str(path),
        "method": "recursive",
        "excluded_dirs": sorted(list(EXCLUDE_DIRS)),
        "files": files,
        "count": len(files)
    }


def main():
    """Main execution function."""
    # Accept optional path argument
    search_path = sys.argv[1] if len(sys.argv) > 1 else "."
    
    # Scan for files
    result = scan_files(search_path)
    
    # Output as JSON
    print(json.dumps(result, indent=2))
    
    # Exit with error if no files found
    if result.get("count", 0) == 0:
        sys.exit(1)
    
    return 0


if __name__ == "__main__":
    sys.exit(main())
