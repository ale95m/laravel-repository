<?php

namespace Easy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MakeEasyModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'easy:model
     {model*}
     {--m|migration}
     {--c|controller}
     {--r|repository}
     {--s|seeder}
     {--e|easy}
     {--R|request}
     ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create one or more models based on Easy arquitecture';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = 0;
        foreach ($this->argument('model') as $model) {
            $model = str_replace('/', '\\', $model);
            $params = ['name' => $model];
            if ($this->option('migration')) {
                $params['-m'] = true;
            }
            if ($this->option('seeder') & !$this->option('repository')) {
                $params['-s'] = true;
            }
            Artisan::call("make:model", $params, $this->output);

            if ($this->option('repository')) {
                Artisan::call('easy:repository', [
                    'name' => [$model . 'Repository'],
                    '--model' => $model
                ], $this->output);
                if ($this->option('seeder')) {
                    Artisan::call('easy:seeder', [
                        'name' => [$model . 'Seeder'],
                        '--repository' => $model . 'Repository'
                    ], $this->output);
                }
            }
            if ($this->option('controller')) {
                Artisan::call('easy:controller', [
                    'name' => [$model . 'Controller'],
                    '--repository' => $model . 'Repository'
                ], $this->output);
            }
            $count++;
        }
        return $count;
    }
}
