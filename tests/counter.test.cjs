/* GlitchEpoch browser-clock tests using Node's built-in test runner. */
'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const script = fs.readFileSync(
  path.join(__dirname, '..', 'assets', 'js', 'glitchepoch.js'),
  'utf8'
);

function makeClock(initialNow, origin) {
  let now = initialNow;
  let advanceTick;
  const nodes = Object.fromEntries(['days', 'hours', 'minutes', 'seconds'].map(function (unit) {
    return [unit, { textContent: '—' }];
  }));
  const element = {
    getAttribute: function (key) { return key === 'data-start' ? origin : null; },
    querySelector: function (selector) {
      const match = selector.match(/^\[data-unit="(days|hours|minutes|seconds)"\]$/);
      return match ? nodes[match[1]] : null;
    }
  };
  const clockDate = class extends Date {
    static now() { return now; }
  };
  const ctx = {
    Date: clockDate,
    Intl,
    Number,
    String,
    Object,
    Math,
    window: {
      matchMedia: function () { return { matches: true }; },
      setInterval: function (fn) { advanceTick = fn; },
      addEventListener: function () {}
    },
    document: {
      readyState: 'complete',
      querySelectorAll: function () { return [element]; },
      addEventListener: function () {},
      hidden: false
    }
  };
  vm.runInNewContext(script, ctx);
  return {
    value: function (unit) { return nodes[unit].textContent; },
    jump: function (newNow) { now = newNow; advanceTick(); }
  };
}

test('clock renders days, hours, minutes and seconds in English number format', function () {
  const start = Date.parse('2010-12-05T02:00:00+01:00');
  const elapsed = (1234 * 86400 + 3 * 3600 + 4 * 60 + 5) * 1000;
  const clock = makeClock(start + elapsed, '2010-12-05T02:00:00+01:00');
  assert.equal(clock.value('days'), '1,234');
  assert.equal(clock.value('hours'), '03');
  assert.equal(clock.value('minutes'), '04');
  assert.equal(clock.value('seconds'), '05');
});

test('clock always derives elapsed time from the absolute timestamp', function () {
  const origin = '2026-11-05T12:00:00Z';
  const start = Date.parse(origin);
  const clock = makeClock(start + 1000, origin);
  assert.equal(clock.value('seconds'), '01');
  clock.jump(start + 61000);
  assert.equal(clock.value('minutes'), '01');
  assert.equal(clock.value('seconds'), '01');
});

test('a future start date is displayed as zero until the instant arrives', function () {
  const origin = '2030-01-01T00:00:00Z';
  const start = Date.parse(origin);
  const clock = makeClock(start - 60_000, origin);
  assert.equal(clock.value('days'), '0');
  assert.equal(clock.value('seconds'), '00');
});
