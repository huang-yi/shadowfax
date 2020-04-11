<?php

namespace HuangYi\Shadowfax\Console;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shadowfax:publish {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all of the Shadowfax resources';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('vendor:publish', [
            '--tag' => 'shadowfax',
            '--force' => $this->option('force'),
        ]);

        $this->copyAndIgnoreConfigFile();
    }

    /**
     * Publish the configuration file.
     *
     * @return void
     */
    protected function copyAndIgnoreConfigFile()
    {
        if (! file_exists(base_path('shadowfax.yml'))) {
            @copy(__DIR__.'/../../shadowfax.yml', base_path('shadowfax.yml'));
        }

        $this->ignoreConfigFile();
    }

    /**
     * Add the configuration file name to .gitignore
     *
     * @return void
     */
    protected function ignoreConfigFile()
    {
        if (! file_exists(base_path('.gitignore'))) {
            return;
        }

        $content = file_get_contents(base_path('.gitignore'));

        if (strpos($content, "shadowfax.yml") !== false) {
            return;
        }

        if (! empty($content) && $content[-1] != "\n") {
            $content .= "\n";
        }

        file_put_contents(base_path('.gitignore'), $content."shadowfax.yml\n");
    }
}
