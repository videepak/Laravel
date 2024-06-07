<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateApiDoc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:docs {version}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API documents';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $version = $this->argument('version');
        $this->call('api:generate', ['--routePrefix' => "{$version}/*", '--force' => 'true']);

        $file = __DIR__ . '/../../../public/docs/index.html';
        if (file_exists($file)) {
            $html = file_get_contents($file);

            $html = str_replace('\n', '<br>', $html);
            $html = str_replace('"{', '{', $html);
            $html = str_replace('}"', '}', $html);
            $html = str_replace('\"', '"', $html);

            file_put_contents($file, $html);
        }
    }
}
