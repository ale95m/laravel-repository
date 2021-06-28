<?php

namespace Easy\Http\Controllers;

use Easy\Exceptions\EasyException;
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

    public function get(Request $request, $file, ?string $type = null)
    {
        $query = File::query();
        if (!is_null($type)) {
            $query->where('type', $type);
        }
        $file = $query->find($file);
        if (is_null($file)) {
            EasyException::throwException(trans('easy::exeptions.not_found.file'));
        }
        if (Storage::exists($file->path)) {
            if ($file->type == File::TEXT) {
                return SendResponse::successData($this->repository->getContent($file));
            } else {
                return Storage::download($file->path);
            }
        } else {
            EasyException::throwException(trans('easy::exeptions.not_found.file'));
        }
    }

    public function getContent(Request $request, $file, ?string $type = null)
    {
        $query = File::query();
        if (!is_null($type)) {
            $query->where('type', $type);
        }
        $file = $query->find($file);
        if (is_null($file)) {
            EasyException::throwException(trans('easy::exeptions.not_found.file'));
        }
        if (Storage::exists($file->path)) {
            return SendResponse::successData($this->repository->getContent($file));
        } else {
            EasyException::throwException(trans('easy::exeptions.not_found.file'));
        }
    }

    public function getBase64(Request $request, $file, ?string $type = null)
    {
        $query = File::query();
        if (!is_null($type)) {
            $query->where('type', $type);
        }
        $file = $query->find($file);
        if (is_null($file)) {
            EasyException::throwException(trans('easy::exeptions.not_found.file'));
        }
        if (Storage::exists($file->path)) {
            return SendResponse::successData(
                base64_encode($this->repository->getContent($file))
            );
        }
    }
}
