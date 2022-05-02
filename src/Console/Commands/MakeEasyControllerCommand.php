<?php

namespace Easy\Console\Commands;

use Easy\Exceptions\EasyException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MakeEasyControllerCommand extends EasyCreateCommand
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'easy:controller {name*} {--r|repository=}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Create a new Easy Controller';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle()
    {
        $count = 0;
        try {
            foreach ($this->argument('name') as $name) {
                $this->make($name);
                $count++;
            }
        } catch (EasyException $exception) {
            $this->error($exception->getMessage());
        }
        return $count;
    }


    public function getNamespace(): string
    {
        return config('easy.proyect_directories.controllers');
    }

    public function getRepositoriesNamespace(): string
    {
        return config('easy.proyect_directories.repositories');
    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/Controller.stub';
    }

    /**
     * Map the stub variables present in stub to its value
     * @param string $seeder
     * @return array
     * @throws EasyException
     */
    public function getStubVariables(string $seeder): array
    {
        $repository_name = $this->option('repository');
        $class_name = $this->files->name($this->getSourceFilePath($seeder));
        $folder = str_replace($class_name, '', $seeder);
        $namespase = $folder != ''
            ? $this->getNamespace() . '\\' . trim($folder, '\\/')
            : $this->getNamespace();
        if (!is_null($repository_name)) {
            $repository = $this->getRepositoriesNamespace() . '\\' . $repository_name;
            if (!$this->files->exists($repository . '.php')) {
                if ($this->confirm("The class $repository_name don't exist. Do you wish to create it?")) {
                    Artisan::call('easy:repository ' . $repository_name);
                }
            } else {
                if (!is_subclass_of('\\' . $repository, \Easy\Repositories\BaseRepository::class)) {
                    EasyException::throwException("The class $repository_name isn't subclass of Easy\Repositories\BaseRepository");
                }
            }
        }
        return [
            'NAMESPACE' => $namespase,
            'CLASS_NAME' => $class_name,
            'REPOSITORY' => $repository_name ? "\\$repository" : '/*TODO: Repository*/'
        ];
    }
}
