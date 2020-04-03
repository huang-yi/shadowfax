<?php

/*
|--------------------------------------------------------------------------
| Create The Shadowfax container
|--------------------------------------------------------------------------
*/

$shadowfax = new HuangYi\Shadowfax\Shadowfax(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Register Shadowfax Commands
|--------------------------------------------------------------------------
*/

$shadowfax->getConsole()->addCommands([
    new HuangYi\Shadowfax\Console\StartCommand($shadowfax),
    new HuangYi\Shadowfax\Console\StopCommand($shadowfax),
    new HuangYi\Shadowfax\Console\ReloadCommand($shadowfax),
]);

/*
|--------------------------------------------------------------------------
| Return The Shadowfax Instance
|--------------------------------------------------------------------------
*/

return $shadowfax;
