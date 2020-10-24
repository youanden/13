<?php

namespace App\Models;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    use SpatialTrait;

    protected $fillable = [
        'name',
        'area',
        'user_id',
        'team_id'
    ];

    protected $spatialFields = [
        'area'
    ];
}
