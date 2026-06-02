<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardLayout extends Model
{
    protected $table      = 'tata_letak_dasbor';
    protected $primaryKey = 'id_tata_letak';
    public    $timestamps = false;

    protected $fillable = ['id_pengguna', 'halaman', 'tata_letak'];

    protected $casts = ['tata_letak' => 'array'];
}
