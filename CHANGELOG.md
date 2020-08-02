# Release Notes

## v2.10.0

### Added

- Add a artisan command `shadowfax:task` to create task class


## v2.9.1

### Fixed

- Fix the file download

### Changed

- Send big response by HTTP chunk


## v2.9.0

### Added

- Add the `AppPoppedEvent`, it will be dispatched after the Laravel Application is popped from the pool
- Add before cleaner
- Add pagination cleaner

### Fixed

- Fix close the WebSocket connection


## v2.8.3

### Fixed

- Fix the WebSocket "handshake" event handler ([#37](https://github.com/huang-yi/shadowfax/issues/37))


## v2.8.2

### Fixed

- Fix the Facade. Clear all of the resolved instance in Facade when popping Laravel Application ([#35](https://github.com/huang-yi/shadowfax/issues/35))


## v2.8.1

### Fixed

- Fix the default value of the configuration option `controllers`


## v2.8.0

### Added

- Add controllers cleaner

### Changed

- Change the controller server configuration key `controller` to `controller_server`


## v2.7.1

### Changed

- The cleaners under the `app/Cleaners/` directory will be loaded automatically even if the `app/Cleaners/` is not added to the `cleaners` configuration


## v2.7.0

### Added

- Add cleaner support
- Add cleaner and WebSocket handler scaffold commands


## v2.6.2

### Fixed

- Fix RedisManager constructor in lower Laravel versions ([#17](https://github.com/huang-yi/shadowfax/issues/17))


## v2.6.1

### Fixed

- Fix override redis manager ([#14](https://github.com/huang-yi/shadowfax/issues/14))


## v2.6.0

### Added

- Add `HuangYi\Shadowfax\HasEventDispatcher` trait
- Add `HuangYi\Shadowfax\Events\AppPushingEvent`, it will be dispatched when recycling app instance

### Changed

- Remove driver support detection when creating database connection pool


## v2.5.1

### Fixed

- Fix determine if a connection is a pool connection

### Changed

- Remove `server.task_worker_num` and `server.task_enable_coroutine` from `shadowfax.yml`
- Add `db_pools` and `redis_pools` to `shadowfax.yml`


## v2.5.0

### Added

- Add redis connection pool support


## v2.4.1

### Fixed

- Fix rebind abstracts named with class name


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
