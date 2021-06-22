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
    'json_numeric_check' => false
];
