<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function credential()
    {
        return $this->belongsTo(Credential::class);
    }
}
