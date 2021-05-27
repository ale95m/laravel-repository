<?php
return [
    'use_auth' => true,
    'user_model' => \Easy\Models\User::class,
    'api_prefix' => 'api',
    'api_middleware' => [],
    'file_path' => 'files',
    'disk' => 'local',
    'get_file_middleware' => [],
    'get_file_prefix' => 'files',
    'email_verification' => false,
    'auth_user_relations' => []
];
