<?php

namespace App\Models;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdhocSubscriber extends Model
{
    use HasFactory;
    use SpatialTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'street_address',
        'city',
        'state',
        'zip',
        'alert_distance',
        'active',
        'location',
    ];

    protected $spatialFields = [
        'location'
    ];
}
