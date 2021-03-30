<?php


namespace Easy\Repositories;

use Easy\Models\File;

class FileRepository extends BaseRepository
{

    /**
     * @inheritDoc
     */
    function getModel()
    {
        return new File();
    }

    public function saveFile($file, string $type, bool $base64Encode = false)
    {
        $directory = File::getDirectory($type);
        $file_name = time();
        $store_path = storage_path($directory);
        $file_name = $this->getUniqueName($directory, $file_name);
        file_put_contents($store_path . $file_name, $file);
        return $this->create(array(
            'path' => $directory . $file_name,
            'type' => $type
        ));
    }

    public function saveBase64Image($file)
    {
        return $this->saveFile($file,File::Base64Image,true);
    }


    public function delete($model, $log = true)
    {
        if (is_numeric($model)) {
            $model = $this->findOrFail($model);
        }
        /** @var File $model */
        $path = storage_path($model->path);
        if (file_exists($path)) {
            unlink($path);
        }
        return parent::delete($model, $log);
    }

    public function base64ToImage(string $base64)
    {
        $data = explode(',', $base64);
        return base64_decode($data[1]);
    }

    private function getUniqueName(string $store_path, $file_name)
    {
        if (file_exists($store_path . $file_name)) {
            return $this->getUniqueName($store_path, $file_name . random_int(1, 9));
        }
        return $file_name;
    }
}
