<?php

namespace Easy\Console\Commands;

use Easy\Exceptions\EasyException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MakeEasyRepositoryCommand extends EasyCreateCommand
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'easy:repository {name*} {--m|model=}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Create a new Easy Repository';

    /**
     * Execute the console command.
     *
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

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath(): string
    {
        return __DIR__ . '/../../stubs/Repository.stub';
    }

    public function getNamespace(): string
    {
        return config('easy.project_directories.repositories');
    }

    public function getModelsNamespace(): string
    {
        return config('easy.project_directories.models');
    }

    /**
     * Map the stub variables present in stub to its value
     * @param string $repository
     * @return array
     * @throws EasyException
     */
    public function getStubVariables(string $repository): array
    {
        $model_name = $this->option('model');
        $class_name = $this->files->name($this->getSourceFilePath($repository));
        $folder = str_replace($class_name, '', $repository);
        $namespase = $folder != ''
            ? $this->getNamespace() . '\\' . trim($folder, '\\/')
            : $this->getNamespace();

        $model_using = 'Illuminate\Database\Eloquent\Model';
        if (!is_null($model_name)) {
            $model = $this->getModelsNamespace() . '\\' . $model_name;
            $model_using = $model;
            $model_exist = false;
            if (!$this->files->exists($model . '.php')) {
                if ($this->confirm("The class $model_name don't exist. Do you wish to create it?")) {
                    Artisan::call('make:model ' . $model_name);
                    $model_exist = true;
                }
            } else {
                if (!is_subclass_of('\\' . $model, \Illuminate\Database\Eloquent\Model::class)) {
                    EasyException::throwException("The $model_name class isn't subclass of Illuminate\Database\Eloquent\Model");
                }
                $model_exist = true;
            }
            if ($model_exist) {
                if ($model_name == $class_name) {
                    $model_name .= 'Model';
                    $model_using .= ' as ' . $model_name;
                }
            }
        }
        return [
            'NAMESPACE' => $namespase,
            'MODEL_USING' => $model_using,
            'MODEL' => class_basename($model_name) ?? 'Model',
            'METHOD_BODY' => $model_name ? 'return new ' . class_basename($model_name) . '();' : "throw new \Exception('Not implemented function');//TODO: return the model",
            'CLASS_NAME' => $class_name
        ];
    }
}
