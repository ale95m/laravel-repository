<?php

namespace ale95m\Easy\Http\Controllers;

use ale95m\Easy\Repositories\LogRepository;

class LogController extends EasyController
{
    public function __construct(LogRepository $repository)
    {
        $this->repository = $repository;
    }
}
