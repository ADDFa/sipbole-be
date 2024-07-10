<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warrant extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ["id"];

    public function warrantsBoats()
    {
        return $this->hasMany(WarrantsBoat::class);
    }

    public function warrantBoat()
    {
        return $this->hasOne(WarrantsBoat::class);
    }
}
