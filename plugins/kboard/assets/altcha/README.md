ALTCHA widget 3.5.0, distributed under the MIT license in LICENSE.txt.
Source: https://github.com/altcha-org/altcha/tree/v3.5.0
The bundled widget includes the Svelte 5.57.0 runtime; its MIT license is in
SVELTE-LICENSE.md.

The KBoard server-side implementation in KBCaptcha.class.php implements the
ALTCHA v3 deterministic PBKDF2/SHA-256 challenge format for compatibility with PHP 7.4.
The PBKDF2 cost is 5000. Target counters are randomly chosen and not sent to
the client: low 500-750, normal 2000-2500, high 4000-5000.
These ranges are tuned for approximate durations, not guaranteed wall-clock times.
Protocol reference: https://github.com/altcha-org/altcha-lib-php
The reference library's MIT license is in ALTCHA-PHP-LICENSE.txt.
