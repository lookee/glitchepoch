/* GlitchEpoch live demo — timestamp supplied by the repository's first commit. */
(function () {
  'use strict';
  const counter = document.getElementById('demo-counter');
  const note = document.getElementById('origin-note');
  const copy = document.getElementById('copy-code');
  const code = document.getElementById('shortcode');

  fetch('./first-commit.json', { cache: 'no-cache' })
    .then(function (response) {
      if (!response.ok) throw new Error('Missing commit metadata');
      return response.json();
    })
    .then(function (metadata) {
      const date = new Date(metadata.first_commit_at);
      if (!Number.isFinite(date.getTime())) throw new Error('Invalid commit date');
      counter.setAttribute('data-start', date.toISOString());
      counter.setAttribute('aria-label', 'Elapsed time since the first commit on ' + date.toUTCString());
      note.textContent = 'Counting from the first Git commit · ' +
        new Intl.DateTimeFormat('en-GB', { dateStyle: 'long', timeStyle: 'short', timeZone: 'UTC' }).format(date) + ' UTC';
      window.dispatchEvent(new Event('glitchepoch:refresh'));
    })
    .catch(function () {
      note.textContent = 'The live counter becomes available after the first GitHub Pages deployment.';
    });

  copy.addEventListener('click', function () {
    if (!navigator.clipboard || !navigator.clipboard.writeText) return;
    navigator.clipboard.writeText(code.textContent).then(function () {
      copy.textContent = 'Copied';
      window.setTimeout(function () { copy.textContent = 'Copy'; }, 1800);
    }).catch(function () { copy.textContent = 'Select and copy'; });
  });
})();
