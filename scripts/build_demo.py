#!/usr/bin/env python3
"""Build a static GitHub Pages demo using the real first commit timestamp."""
from __future__ import annotations
import argparse
from datetime import datetime, timezone
import json
from pathlib import Path
import shutil
import subprocess
import sys

ROOT = Path(__file__).resolve().parents[1]


def first_commit_time() -> str | None:
    """Return UTC ISO-8601 timestamp of the repository's oldest root commit."""
    try:
        roots = subprocess.check_output(
            ['git', 'rev-list', '--max-parents=0', 'HEAD'], cwd=ROOT, text=True, stderr=subprocess.DEVNULL
        ).strip().splitlines()
        if not roots:
            return None
        timestamps = []
        for sha in roots:
            value = subprocess.check_output(
                ['git', 'show', '-s', '--format=%cI', sha], cwd=ROOT, text=True
            ).strip()
            timestamps.append(datetime.fromisoformat(value).astimezone(timezone.utc))
        return min(timestamps).isoformat().replace('+00:00', 'Z')
    except (OSError, subprocess.CalledProcessError, ValueError):
        return None


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output', default='site', help='Destination for built site')
    parser.add_argument('--require-git', action='store_true', help='Fail if no Git commit exists')
    args = parser.parse_args()

    output = Path(args.output).resolve()
    if output == ROOT or output == ROOT / 'docs':
        parser.error('Output must not overwrite the repository or docs directory')
    if output.exists():
        shutil.rmtree(output)
    shutil.copytree(ROOT / 'docs', output)
    assets = output / 'assets'
    assets.mkdir(exist_ok=True)
    shutil.copy2(ROOT / 'assets/css/glitchepoch.css', assets / 'glitchepoch.css')
    shutil.copy2(ROOT / 'assets/js/glitchepoch.js', assets / 'glitchepoch.js')

    started = first_commit_time()
    if started:
        (output / 'first-commit.json').write_text(
            json.dumps({'first_commit_at': started}, indent=2) + '\n', encoding='utf-8'
        )
        print('Demo origin: first Git commit at', started)
    elif args.require_git:
        print('ERROR: no Git history found; cannot set first commit time.', file=sys.stderr)
        return 1
    else:
        print('No Git history yet; demo will show a deployment message.')
    print('Site generated in', output)
    return 0


if __name__ == '__main__':
    sys.exit(main())
