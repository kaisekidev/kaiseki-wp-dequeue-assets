# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.0 - 2026-06-01

First tagged release.

### Added

- `DequeueAssets` hook provider — dequeues and deregisters configured script/style handles on the
  front end, each gated by `true` or a `kaiseki/wp-context` `ContextFilterInterface` (or a pipeline of
  them). `ConfigProvider` and `DequeueAssetsFactory` wire it from the `dequeue_assets` config key.

### Changed

- PHP requirement is `^8.2` (PHP 8.4 is the primary target).
- Modernized the dev toolchain (PHPStan 2, PHPUnit 11 schema, composer-require-checker 4) and depend
  on `kaiseki/php-coding-standard: ^1.0` with the shared PHPStan config; `kaiseki/config` and
  `kaiseki/wp-hook` pinned to `^2.0`, `kaiseki/wp-context` to `^1.0`. CI now runs via the reusable
  workflow in `kaisekidev/.github`.
