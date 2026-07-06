<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function upazilas()
    {
        return $this->hasMany(Upazila::class);
    }

    public function imams()
    {
        return $this->hasMany(Imam::class);
    }
    public function hujurs()
    {
        return $this->hasMany(Hujur::class);
    }
    public function maszids()
    {
        return $this->hasMany(Maszid::class);
    }
    public function madrasas()
    {
        return $this->hasMany(Madrasa::class);
    }

}
