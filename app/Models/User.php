<?php

namespace App\Models;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="Model data user",
 *     @OA\Property(property="id", type="integer", description="ID user"),
 *     @OA\Property(property="name", type="string", description="Nama user"),
 *     @OA\Property(property="email", type="string", format="email", description="Email user"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, description="Tanggal verifikasi email"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Tanggal dibuat"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Tanggal diupdate")
 * )
 */
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nik',
        'kontak',
        'name',
        'email',
        'sso_id',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function providers()
    {
        return $this->hasMany(Provider::class, 'id_user', 'id');
    }
}
