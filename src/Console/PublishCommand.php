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
        $this->copyResources($this->option('force'));

        $this->copyAndIgnoreConfigFile();

        $this->info('Publishing complete.');
    }

    /**
     * Copy resources.
     *
     * @param  bool  $force
     * @return void
     */
    protected function copyResources($force)
    {
        $map = [
            __DIR__.'/../../.watch' => base_path('.watch'),
            __DIR__.'/../../shadowfax' => base_path('shadowfax'),
            __DIR__.'/../../shadowfax.yml' => base_path('shadowfax.yml.example'),
            __DIR__.'/../../bootstrap/shadowfax.php' => base_path('bootstrap/shadowfax.php'),
        ];

        foreach ($map as $from => $to) {
            $this->copyFile($from, $to, $force);
        }
    }

    /**
     * Copy file.
     *
     * @param  string  $from
     * @param  string  $to
     * @param  bool  $force
     * @return void
     */
    protected function copyFile($from, $to, $force)
    {
        if ($force || ! file_exists($to)) {
            copy($from, $to);

            $this->status($from, $to);
        }
    }

    /**
     * Publish the configuration file.
     *
     * @return void
     */
    protected function copyAndIgnoreConfigFile()
    {
        $from = __DIR__.'/../../shadowfax.yml';
        $to = base_path('shadowfax.yml');

        if (! file_exists($to)) {
            copy($from, $to);

            $this->status($from, $to);
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

        $this->line('<info>Add</info> <comment>[shadowfax.yml]</comment> <info>To</info> <comment>[.gitignore]</comment>');
    }

    /**
     * Write a status message to the console.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    protected function status($from, $to)
    {
        $from = str_replace(base_path(), '', realpath($from));

        $to = str_replace(base_path(), '', realpath($to));

        $this->line('<info>Copied File</info> <comment>['.$from.']</comment> <info>To</info> <comment>['.$to.']</comment>');
    }
}
