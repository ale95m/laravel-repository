<?php
return [
    'use_auth' => true,
    'api_prefix' => 'api',
    'api_middleware' => [],
    'auth_middleware' => ['auth'],

    'email_verification' => false,
    'auth_user_relations' => [],
    'restore_password_route' => null,

    'files' => [
        'path' => 'files',
        'disk' => 'local',
        'use_routes' => false,
        'middlewares' => [],
        'prefix' => 'files',
    ],

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
