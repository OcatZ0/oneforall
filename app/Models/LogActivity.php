<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'log_aktivitas';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'id_log';

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
        'id_pengguna',
        'aktivitas',
        'tanggal',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
        ];
    }

    /**
     * Relationship: Get the user who performed this activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna', 'id_pengguna');
    }
}
