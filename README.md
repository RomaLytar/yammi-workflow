# Yammi Workflow - Laravel Workflow, State Machine & Approval Engine

[![Latest Version on Packagist](https://img.shields.io/packagist/v/romalytar/yammi-workflow-laravel.svg)](https://packagist.org/packages/romalytar/yammi-workflow-laravel)
[![CI](https://github.com/RomaLytar/yammi-workflow/actions/workflows/ci.yml/badge.svg?branch=dev)](https://github.com/RomaLytar/yammi-workflow/actions/workflows/ci.yml)
[![Tested on](https://img.shields.io/badge/tests-PHP%208.1%20%7C%208.2%20%7C%208.3-777BB4?logo=php&logoColor=white)](https://github.com/RomaLytar/yammi-workflow/actions/workflows/ci.yml)
[![License](https://img.shields.io/packagist/l/romalytar/yammi-workflow-laravel.svg)](https://packagist.org/packages/romalytar/yammi-workflow-laravel)

**Model business processes as explicit states and guarded transitions** - then drive multi-step approvals, run side effects on Laravel queues, and surface everything in a Filament UI.

Instead of a raw `status` string:

```php
$invoice->status = 'approved';
```

you get a real state machine that refuses illegal moves:

```php
$invoice->transitionTo('approved');   // allowed: pending -> approved
$invoice->transitionTo('draft');      // throws: paid -> draft is not a defined transition
```

## Status

Early development. The public API is still taking shape - see the roadmap. Built on standard Laravel building blocks (Eloquent, events, queues, notifications) with a DDD core.

## Requirements

- PHP `^8.1`
- Laravel `^9.0 || ^10.0 || ^11.0 || ^12.0 || ^13.0`

## License

MIT. See [LICENSE](LICENSE).
