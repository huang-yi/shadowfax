<?php

namespace HuangYi\Shadowfax;

class Config
{
    /**
     * The container.
     *
     * @var \HuangYi\Shadowfax\Shadowfax
     */
    protected $shadowfax;

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
    protected $defaultPath = __DIR__.'/../shadowfax.ini';

    /**
     * Config constructor.
     *
     * @param  string  $userPath
     * @param  \HuangYi\Shadowfax\Shadowfax  $shadowfax
     * @return void
     */
    public function __construct(string $userPath = null, Shadowfax $shadowfax = null)
    {
        $this->shadowfax = $shadowfax ?: new Shadowfax;

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

        if ($userPath && file_exists($userPath)) {
            $userItems = parse_ini_file($userPath, true);

            $items = array_merge($items, $userItems);
        }

        $this->items = $this->convertEmptyStringsToNull($items);
    }

    /**
     * Convert empty strings to null.
     *
     * @param  array  $items
     * @return array
     */
    protected function convertEmptyStringsToNull(array $items)
    {
        foreach ($items as $key => $value) {
            if (is_array($value)) {
                $items[$key] = $this->convertEmptyStringsToNull($value);
            } elseif (empty($value)) {
                $items[$key] = null;
            }
        }

        return $items;
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
