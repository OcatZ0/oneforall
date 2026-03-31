<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'pengguna';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'id_pengguna';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'kata_sandi',
        'peran',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'kata_sandi',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_dibuat' => 'datetime',
        ];
    }

    /**
     * Get the password attribute for authentication.
     */
    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    /**
     * Relationship: Get all agents for this user.
     */
    public function agents()
    {
        return $this->hasMany(Agent::class, 'id_pengguna', 'id_pengguna');
    }

    /**
     * Relationship: Get all activity logs for this user.
     */
    public function activities()
    {
        return $this->hasMany(LogActivity::class, 'id_pengguna', 'id_pengguna');
    }
}

