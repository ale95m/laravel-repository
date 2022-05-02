<?php

namespace Easy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

abstract class EasyCreateCommand extends Command
{
    /**
     * Filesystem instance
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }


    protected function make(string $name)
    {
        $name = str_replace('/', '\\', $name);
        $path = $this->getSourceFilePath($name);
        if (!$this->files->exists($path)) {
            $this->makeDirectory(dirname($path));
            $contents = $this->getSourceFile($name);
            $this->files->put($path, $contents);
            $this->info("{$name} created successfully");
        } else {
            $this->error("{$name} already exits");
        }
    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public function getSourceFilePath(string $name): string
    {
        return base_path($this->getNamespace()) . '\\' . $name . '.php';
    }

//    protected function getClasssName(string $name):string
//    {
//
//    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     * @return string
     */
    protected final function makeDirectory(string $path): string
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }
        return $path;
    }

    /**
     * Get the stub path and the stub variables
     * @param string $class_name
     * @return string
     */
    public function getSourceFile(string $class_name): string
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables($class_name));
    }

    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param string $stub
     * @param array $stubVariables
     * @return string
     */
    public function getStubContents(string $stub, array $stubVariables = []): string
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$' . $search . '$', $replace, $contents);
        }
        return $contents;
    }

    /**
     * Return the namespace for new class
     * @return string
     */
    protected abstract function getNamespace(): string;

    /**
     * Return the stub file path
     * @return string
     */
    protected abstract function getStubPath(): string;

    /**
     * Map the stub variables present in stub to its value
     * @param string $name
     * @return array
     */
    public abstract function getStubVariables(string $file): array;
}
