<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    public static function months()
    {
        return [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "Mei",
            "Jun",
            "Jul",
            "Agu",
            "Sep",
            "Okt",
            "Nov",
            "Des"
        ];
    }

    public function activityReports()
    {
        return $this->hasMany(ActivityReport::class);
    }

    public function warrant()
    {
        return $this->belongsTo(Warrant::class);
    }

    public function sarDocumentations()
    {
        return $this->hasMany(SarDocumentation::class);
    }
}
