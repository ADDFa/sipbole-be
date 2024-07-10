<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantsBoat extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public function boat()
    {
        return $this->belongsTo(Boat::class);
    }

    public function warrant()
    {
        return $this->belongsTo(Warrant::class);
    }
}
