<?php

namespace Easy\Http\Controllers;

use Easy\Repositories\LogRepository;

class LogController extends EasyController
{
    public function __construct(LogRepository $repository)
    {
        $this->repository = $repository;
    }
}
