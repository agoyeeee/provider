<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tiang extends Model
{
    protected $table = 'tiang';

    public const STATUS_IZIN = ['berizin', 'tidak_berizin'];

    protected $fillable = ['provider_id', 'n_provider', 'odp', 'status_izin'];

    protected $casts = ['odp' => 'boolean'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
