<?php

namespace HuangYi\Shadowfax\Laravel;

use Illuminate\Contracts\Container\Container;

trait RebindsAbstracts
{
    /**
     * Rebind the application's abstracts.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @param  array $abstracts
     * @return void
     */
    public function rebindAbstracts(Container $app, array $abstracts)
    {
        foreach ($abstracts as $abstract) {
            $abstract = trim($abstract, "\\");

            if ($app->bound($abstract)) {
                static::rebindAbstract($app, $abstract);
            }
        }
    }

    /**
     * Rebind abstract.
     *
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @param  string  $abstract
     * @return void
     */
    public function rebindAbstract(Container $app, $abstract)
    {
        $abstract = $app->getAlias($abstract);
        $binding = $app->getBindings()[$abstract] ?? null;

        unset($app[$abstract]);

        if ($binding) {
            $app->bind($abstract, $binding['concrete'], $binding['shared']);
        }
    }
}
