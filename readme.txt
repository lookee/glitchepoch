=== GlitchEpoch ===
Contributors: lookee
Tags: elapsed time, counter, days, clock, timer, shortcode
Requires at least: 6.2
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A beautiful, lightweight elapsed-time counter for WordPress. Four clear values, no tracking, no dependencies.

== Description ==

Created and maintained by Luca Amore (https://www.lucaamore.com/).
GitHub profile: https://github.com/lookee
Source code and documentation: https://github.com/lookee/glitchepoch

GlitchEpoch shows how much time has passed since a specific date: Days, Hours, Minutes, and Seconds. The minimalist counter is responsive, accessible, and friendly to page caching.

Features:
* Refined white interface with restrained motion.
* Elapsed days, hours, minutes, and seconds.
* Shortcode for posts, pages, and widgets.
* IANA timezone support and daylight saving time awareness.
* Optional accent color.
* Accessible timer semantics and reduced-motion support.
* No external scripts, cookies, analytics, or network requests.

== Installation ==

1. Install the plugin ZIP via Plugins > Add New Plugin > Upload Plugin.
2. Activate GlitchEpoch.
3. Go to Settings > GlitchEpoch for an example.
4. Add a Shortcode block to a post or page and paste:

`[glitchepoch start="2010-12-05 02:00:00" timezone="Europe/Rome"]`

== Frequently Asked Questions ==

= What date format should I use? =

YYYY-MM-DD HH:MM or YYYY-MM-DD HH:MM:SS. The date represents local time in the specified timezone.

= Do I need a timezone? =

No. By default the shortcode uses your WordPress site timezone (Settings > General). Explicit IANA zones, such as Europe/Rome, are recommended for reusable content.

= Can I customize the accent color? =

Yes: `[glitchepoch start="2020-01-01 00:00" timezone="UTC" accent="#2563EB"]`

= Does this track visitors? =

No. The plugin runs entirely in the browser and does not set cookies or contact third-party services.

== Credits ==

Author and maintainer: Luca Amore
Personal website: https://www.lucaamore.com/
GitHub: https://github.com/lookee
Project repository: https://github.com/lookee/glitchepoch

== Changelog ==

= 1.0.0 =
* Initial open-source release.
