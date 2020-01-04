<?php

namespace HuangYi\Shadowfax\Support;

class Config
{
    /**
     * The configuration items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * The default config path.
     *
     * @var string
     */
    protected $defaultPath = __DIR__.'/../../shadowfax.ini';

    /**
     * Config constructor.
     *
     * @param  string  $userPath
     * @return void
     */
    public function __construct(string $userPath = null)
    {
        $this->init($userPath);
    }

    /**
     * Initialize the configurations.
     *
     * @param $userPath
     * @return void
     */
    protected function init($userPath)
    {
        $items = parse_ini_file($this->defaultPath, true);

        if ($userPath) {
            $userItems = parse_ini_file($userPath, true);

            $items = array_merge($items, $userItems);
        }

        $this->items = $items;
    }

    /**
     * Get the configuration item.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->items[$key] ?? $default;
    }

    /**
     * Get all configurations.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }
}
