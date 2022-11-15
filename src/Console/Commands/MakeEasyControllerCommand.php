<?php

namespace Easy\Console\Commands;

use Easy\Exceptions\EasyException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Psy\Util\Str;

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
        return config('easy.project_directories.controllers');
    }

    public function getRepositoriesNamespace(): string
    {
        return config('easy.project_directories.repositories');
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
        $model_name = '/*TODO: model class*/';
        $model_param = 'model';
        $model_using = 'Illuminate\Database\Eloquent\Model';
        if (!is_null($repository_name)) {
            $repository_using = $this->getRepositoriesNamespace() . '\\' . $repository_name;
            $repository_exist = false;
            if (!$this->files->exists($repository_using . '.php')) {
                if ($this->confirm("The class $repository_name don't exist. Do you wish to create it?")) {
                    Artisan::call('easy:repository ' . $repository_name);
                    $repository_exist = true;
                }
            } else {
                if (!is_subclass_of('\\' . $repository_using, \Easy\Repositories\BaseRepository::class)) {
                    EasyException::throwException("The class $repository_name isn't subclass of Easy\Repositories\BaseRepository");
                }
                $repository_exist = true;
            }
            if ($repository_exist) {
                try {
                    $repo = new $repository_using();
                    $model_using = $repo->getModel()::class;
                    $model_name = class_basename($model_using);
                    $model_param = lcfirst($model_name);
                } catch (\Exception $exception) {
                }
            }
            if ($repository_name == $model_name) {
                $repository_name .= 'Repository';
                $repository_using .= ' as ' . $repository_name;
            }

        }
        return [
            'NAMESPACE' => $namespase,
            'CLASS_NAME' => $class_name,
            'REPOSITORY' => $repository_name? class_basename($repository_name) : '/*TODO: Repository class*/',
            'REPOSITORY_USING' => $repository_name ? "$repository_using" : $this->getRepositoriesNamespace(),
            'MODEL_USING' => $model_using,
            'MODEL' => $model_name,
            'MODEL_PARAM' => $model_param
        ];
    }
}
