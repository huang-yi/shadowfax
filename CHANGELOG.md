# Release Notes


## v2.0.1

## Added

- Support more versions for Laravel (5.5 - 5.8)

## Fixed

- Fix `shadowfax:publish` Artisan command in Lumen


## v2.0.0

## Added

- Add a bootstrap file `bootstrap/shadowfax.php`
- Add the WebSocket server
- Add the `shadowfax:publish` Artisan command

### Changed

- Use event dispatcher to dispatch Swoole server events
- Use `shadowfax.yml` as the configuration file (replaced `shadowfax.ini`)
- Publish the console script `shadowfax` to project root
