# Release Notes


## v2.4.0

### Added

- Add mysql connection pool support


## v2.3.0

### Added

- Add Swoole server instance for starting event
- Add component to extend Shadowfax
- Add singleton method for container
- Add support for WebSocket handshake event
- Add `events` configuration option for customizing the events Swoole server listened
- Add a new event `HuangYi\Shadowfax\Events\FrameworkBootstrappedEvent`, it will be dispatched when the Laravel framework bootstrapped

### Changed

- Remove the WebSocket message configuration option
- Use the return value of `HuangYi\Shadowfax\Contracts\WebSocket\Handler::message()` method first to create the message instance (if any)
- Close the WebSocket connection when receiving an invalid message


## v2.2.0

### Added

- Add a default value for the second parameter of JsonMessage constructor
- Add a bootstrap file for phpunit
- Add method `HuangYi\Shadowfax\WebSocket\ConnectionCollection::all()`

### Fixed

- Fix `HuangYi\Shadowfax\WebSocket\Connection::close()`
- Fix `--watch` option in `start` command

### Changed

- Remove the dev package `huang-yi/swooleunit`


## v2.1.0

### Added

- Add priority for listeners

### Changed

- Register shadowfax services in different environments


## v2.0.1

### Added

- Support more versions for Laravel (5.5 - 5.8)

### Fixed

- Fix `shadowfax:publish` Artisan command in Lumen


## v2.0.0

### Added

- Add a bootstrap file `bootstrap/shadowfax.php`
- Add the WebSocket server
- Add the `shadowfax:publish` Artisan command

### Changed

- Use event dispatcher to dispatch Swoole server events
- Use `shadowfax.yml` as the configuration file (replaced `shadowfax.ini`)
- Publish the console script `shadowfax` to project root
