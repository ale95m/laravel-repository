<?php

namespace Easy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string|mixed path
 * @property mixed type
 */
class File extends Model
{
    use HasFactory;

    const Image = 'Image';
    const TEXT = 'Text';
    const FILE = 'File';

    protected $fillable = [
        'path',
        'type',
        'is_text'
    ];

    public function getTable()
    {
        return config('easy.tables.files','easy_files');
    }
}
