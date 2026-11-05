/* GlitchEpoch — tiny, dependency-free elapsed-time clock. GPL-2.0-or-later. */
(function () {
  'use strict';

  const motionQuery = window.matchMedia
    ? window.matchMedia('(prefers-reduced-motion: reduce)')
    : { matches: true };

  function display(value, unit) {
    return unit === 'days'
      ? new Intl.NumberFormat('en-US').format(value)
      : String(value).padStart(2, '0');
  }

  function tick() {
    const now = Date.now();
    document.querySelectorAll('.glitchepoch[data-start]').forEach(function (counter) {
      const start = Date.parse(counter.getAttribute('data-start'));
      if (!Number.isFinite(start)) return;

      // Recompute from wall-clock time to avoid timer drift or tab throttling.
      const total = Math.max(0, Math.floor((now - start) / 1000));
      const values = {
        days: Math.floor(total / 86400),
        hours: Math.floor((total % 86400) / 3600),
        minutes: Math.floor((total % 3600) / 60),
        seconds: total % 60
      };

      Object.keys(values).forEach(function (unit) {
        const el = counter.querySelector('[data-unit="' + unit + '"]');
        if (!el) return;
        const next = display(values[unit], unit);
        if (el.textContent === next) return;
        el.textContent = next;

        if (unit === 'seconds' && !motionQuery.matches && el.animate) {
          el.animate(
            [
              { opacity: 0.52, transform: 'translateY(3px)' },
              { opacity: 1, transform: 'translateY(0)' }
            ],
            { duration: 260, easing: 'ease-out' }
          );
        }
      });
    });
  }

  function init() {
    tick();
    window.setInterval(tick, 250);
    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) tick();
    });
    window.addEventListener('glitchepoch:refresh', tick);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
