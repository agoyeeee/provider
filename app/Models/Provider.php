<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $table = 'providers';

    protected $fillable = [
        'provinsi',
        'kota',
        'kecamatan',
        'kelurahan',
        'k_ref_wil',
        'utilitas',
        'n_provider',
        'odp',
        'sijali',
        'sijali_link',
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

    public function tiang()
    {
        return $this->hasMany(Tiang::class);
    }

    /** Nama provider semua tiang, mis. "Link Net, Telkom". Wajib eager load 'tiang'. */
    public function getNProviderLabelAttribute(): string
    {
        return $this->tiang->pluck('n_provider')->implode(', ');
    }

    /** Status ODP per tiang, mis. "Link Net Ada, Telkom Tidak Ada". Wajib eager load 'tiang'. */
    public function getOdpLabelAttribute(): string
    {
        return $this->tiang
            ->map(fn($t) => $t->n_provider . ($t->odp ? ' Ada' : ' Tidak Ada'))
            ->implode(', ');
    }

    /** Tiang sebagai array untuk frontend. Wajib eager load 'tiang'. */
    public function tiangList(): array
    {
        return $this->tiang
            ->map(fn($t) => $t->only(['id', 'n_provider', 'odp', 'status_izin']))
            ->values()
            ->all();
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
        return $query->whereHas('tiang', fn($q) => $q->where('n_provider', $provider));
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
