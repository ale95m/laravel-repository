<?php
return [
    'use_auth' => true,
    'user_model' => \Easy\Models\User::class,
    'api_prefix' => 'api',
    'api_middleware' => [],
    'file_path' => 'files',
    'disk' => 'local',
    'use_file_routes' => false,
    'get_file_middleware' => [],
    'get_file_prefix' => 'files',
    'email_verification' => false,
    'auth_user_relations' => [],
    'json_numeric_check' => false,
    'restore_password_route' => null,

    'pagination' => [
        'input' => [
            'items_per_page' => 'itemsPerPage',
            'current_page' => 'page',
        ],
        'output' => [
            'items_per_page' => 'itemsPerPage',
            'current_page' => 'page',
            'items_length' => 'itemsLength',
            'page_count' => 'pageCount',
        ]
    ],
    'query' => [
        'sort_by' => 'sort_by',
        'sort_asc' => 'sort_asc',
        'only_deleted' => 'only_deleted',
        'with_deleted' => 'with_deleted',
        'searchBy' => 'searchBy',
    ],
    'project_directories' => [
        'models' => 'App\\Models',
        'controllers' => 'App\\Http\\Controllers',
        'repositories' => 'App\\Repositories',
        'seeders' => 'Database\\Seeders',
    ],
];
