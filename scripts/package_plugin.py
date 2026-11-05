#!/usr/bin/env python3
"""Build an installable WordPress ZIP (no development files included)."""
from __future__ import annotations
import argparse
from pathlib import Path
import zipfile

ROOT = Path(__file__).resolve().parents[1]
FILES = [
    'glitchepoch.php',
    'readme.txt',
    'LICENSE',
    'assets/css/glitchepoch.css',
    'assets/js/glitchepoch.js',
]


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output', default='dist/glitchepoch.zip')
    args = parser.parse_args()
    target = Path(args.output).resolve()
    target.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(target, 'w', compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for name in FILES:
            src = ROOT / name
            if not src.is_file():
                parser.error('Missing plugin file: ' + name)
            archive.write(src, arcname='glitchepoch/' + name)
    print('WordPress plugin ZIP:', target)


if __name__ == '__main__':
    main()
