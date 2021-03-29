<?php

namespace ale95m\Easy\Models;

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
    const Base64Image = 'Base64Image';
    const Doc = 'Doc';
    const File = 'File';

    protected $fillable = [
        'path',
        'type'
    ];

    /**
     * @param string $type
     * @return string
     */
    public static function getDirectory(string $type)
    {
        switch ($type) {
            case 'Image':
            case 'Base64Image':
            {
                return 'images/';
            }
            case 'Doc':
            {
                return 'docs/';
            }
            default:
                return 'files/';
        }
    }
}
