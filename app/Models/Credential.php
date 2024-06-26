<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credential extends Model
{
    use HasFactory;

    protected $guarded = ["id"];
    protected $hidden = ["password"];

    public $timestamps = false;

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
