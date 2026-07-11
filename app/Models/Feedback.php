<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['type', 'message', 'is_read'];
    
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }
}
