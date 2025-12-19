<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $table = 'providers';

    protected $fillable = [
        'fid',
        'provinsi',
        'kota',
        'kecamatan',
        'kelurahan',
        'k_ref_wil',
        'utilitas',
        'n_provider',
        'odp',
        'sjalu_21',
        'x',
        'y',
        'foto',
        'tgl_survey',
        'id_user',
    ];

    protected $casts = [
        'x' => 'decimal:10',
        'y' => 'decimal:10',
        'tgl_survey' => 'date',
    ];

    /**
     * Get the user that created this provider data.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    /**
     * Scope to filter by provinsi
     */
    public function scopeByProvinsi($query, string $provinsi)
    {
        return $query->where('provinsi', $provinsi);
    }

    /**
     * Scope to filter by kota
     */
    public function scopeByKota($query, string $kota)
    {
        return $query->where('kota', $kota);
    }

    /**
     * Scope to filter by kecamatan
     */
    public function scopeByKecamatan($query, string $kecamatan)
    {
        return $query->where('kecamatan', $kecamatan);
    }

    /**
     * Scope to filter by provider name
     */
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('n_provider', $provider);
    }

    /**
     * Get foto URL attribute
     */
    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }
        return null;
    }
}
