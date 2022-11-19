<?php


namespace Easy\Repositories;

use Easy\Exceptions\EasyException;
use Easy\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileRepository extends EasyRepository
{

    /**
     * @inheritDoc
     */
    function getModel():File
    {
        return new File();
    }

    private function save($file, ?string $type, string $directory, bool $is_text = false, bool $base64Encode = false)
    {
        $directory = $this->getBasePath($directory);
        $file_name = Str::uuid();
        if ($is_text) {
            Storage::disk($this->getDisk())->append($directory . $file_name, $file);
        } else {
            Storage::disk($this->getDisk())->put($directory . $file_name,
                $base64Encode ? $this->base64ToImage($file) : $file);
        }

        return $this->create(array(
            'path' => $directory . $file_name,
            'type' => $type,
            'is_text' => $is_text
        ));
    }

    public function saveBase64File($file, string $type = null, $path = '')
    {
        return $this->save($file, $type, $path, false, true);
    }

    public function saveTextFile($file, string $type = null, $path = '')
    {
        return $this->save($file, $type, $path, true);
    }

    public function saveFile($file, string $type = null, $path = '')
    {
        return $this->save($file, $type, $path, false);
    }

    public function getFile(File $file)
    {
        if (!Storage::disk($this->getDisk())->exists($file->path)) {
            EasyException::throwException(trans('easy::exeptions.not_found.file'));
        }
        return Storage::disk($this->getDisk())->get($file->path);
    }

    public function getContent(File $file)
    {
        $file = storage_path('app/' . $file->path);
        return file_get_contents($file);
    }

    private function getDisk()
    {
        return config('easy.files.disk');
    }

    private function getBasePath(string $directory)
    {
        if ($directory != '') {
            $directory .= '/';
        }
        $base_path = config('easy.files.path');
        if ($base_path != '/' & $base_path != '') {
            $base_path .= '/';
        }
        return $base_path . $directory;
    }

    public function delete($model, $log = true)
    {
        if (is_numeric($model)) {
            $model = $this->findOrFail($model);
        }
        /** @var File $model */
        if (Storage::disk($this->getDisk())->exists($model->path)) {
            Storage::disk($this->getDisk())->delete($model->path);
        }
        return parent::delete($model, $log);
    }

    public function base64ToImage(string $base64)
    {
        $data = explode(',', $base64);
        return base64_decode($data[1]);
    }
}
