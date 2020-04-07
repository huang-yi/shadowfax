# Release Notes


## v2.0.0

## Added

- Add bootstrap file `bootstrap/shadowfax.php`
- Add the WebSocket server

### Changed

- Use `symfony/event-dispatcher` component to dispatch Swoole server events
- Use `shadowfax.yml` as the configuration file (replaced `shadowfax.ini`)
- Publish the console script `shadowfax` to project root
