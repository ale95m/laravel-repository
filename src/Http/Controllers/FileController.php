<?php

namespace Easy\Http\Controllers;

use Easy\Http\Responses\SendResponse;
use Easy\Models\File;
use Easy\Repositories\FileRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends EasyController
{
    protected $repository;

    public function __construct(FileRepository $fileRepository)
    {
        $this->repository = $fileRepository;
     }

    public function get(Request $request, File $file)
    {
        if (Storage::exists($file->path)) {
            if ($file->type == File::TEXT) {
                return SendResponse::successData($this->repository->getContent($file));
            } else {
                return Storage::download($file->path);
//                return response()->download($this->repository->getFile($file));
            }
        } else {
            throw new \Exception(trans('easy::exeptions.not_found.file'));
        }
    }
}
