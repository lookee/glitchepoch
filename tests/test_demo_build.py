"""Verify the Pages demo derives its origin from real Git history."""
from __future__ import annotations

import json
import os
from pathlib import Path
import shutil
import subprocess
import tempfile
import unittest

ROOT = Path(__file__).resolve().parents[1]


class DemoBuildTest(unittest.TestCase):
    def test_first_commit_is_preserved_after_followup_commits(self) -> None:
        with tempfile.TemporaryDirectory(prefix='glitchepoch-demo-test-') as temp:
            repo = Path(temp) / 'repo'
            shutil.copytree(ROOT, repo, ignore=shutil.ignore_patterns('.git', 'dist', 'site', '__pycache__'))

            def git(*args: str, commit_time: str | None = None) -> None:
                env = os.environ.copy()
                if commit_time:
                    env['GIT_AUTHOR_DATE'] = commit_time
                    env['GIT_COMMITTER_DATE'] = commit_time
                subprocess.run(['git', *args], cwd=repo, env=env, check=True, capture_output=True)

            git('init', '-q', '-b', 'main')
            git('config', 'user.name', 'GlitchEpoch Test')
            git('config', 'user.email', 'test@example.invalid')
            git('add', '.')
            git('commit', '-qm', 'Initial release', commit_time='2026-11-05T16:40:00+00:00')
            (repo / 'followup.txt').write_text('A second commit.\n', encoding='utf-8')
            git('add', 'followup.txt')
            git('commit', '-qm', 'Follow-up', commit_time='2026-11-06T16:40:00+00:00')

            target = Path(temp) / 'site'
            subprocess.run(
                ['python3', 'scripts/build_demo.py', '--output', str(target), '--require-git'],
                cwd=repo, check=True, capture_output=True, text=True
            )
            metadata = json.loads((target / 'first-commit.json').read_text(encoding='utf-8'))
            self.assertEqual('2026-11-05T16:40:00Z', metadata['first_commit_at'])
            self.assertTrue((target / 'assets' / 'glitchepoch.js').is_file())
            self.assertTrue((target / 'index.html').is_file())


if __name__ == '__main__':
    unittest.main()
