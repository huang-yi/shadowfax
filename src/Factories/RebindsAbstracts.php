<?php

namespace HuangYi\Shadowfax\Factories;

use Illuminate\Contracts\Container\Container;

trait RebindsAbstracts
{
    /**
     * Rebind the application's abstracts.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @return void
     */
    protected function rebindAbstracts(Container $app)
    {
        if (! $app->bound('config')) {
            return;
        }

        $resets = $app['config']['shadowfax.abstracts'] ?: [];

        foreach ($resets as $item) {
            if ($app->bound($item)) {
                static::rebindAbstract($app, $item);
            }
        }
    }

    /**
     * Rebind abstract.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @param  string  $name
     * @return void
     */
    protected function rebindAbstract(Container $app, $name)
    {
        $abstract = $app->getAlias($name);
        $binding = $app->getBindings()[$abstract] ?? null;

        unset($app[$abstract]);

        if ($binding) {
            $app->bind($abstract, $binding['concrete'], $binding['shared']);
        }
    }
}
