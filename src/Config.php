<?php

namespace HuangYi\Shadowfax;

class Config
{
    /**
     * The configuration items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * The configuration file in project.
     *
     * @var string
     */
    protected $projectPath = __DIR__.'/../../../../shadowfax.ini';

    /**
     * The configuration file in package.
     *
     * @var string
     */
    protected $packagePath = __DIR__.'/../shadowfax.ini';

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
        if ($userPath) {
            $path = $userPath;
        } elseif (file_exists($this->projectPath)) {
            $path = $this->projectPath;
        } else {
            $path = $this->packagePath;
        }

        $this->items = $this->convertEmptyStringsToNull(
            parse_ini_file($path, true)
        );
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
            } elseif (! is_numeric($value) && empty($value)) {
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
