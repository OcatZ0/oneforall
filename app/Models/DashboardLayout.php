<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardLayout extends Model
{
    protected $table = 'dashboard_layouts';

    protected $fillable = ['id_pengguna', 'page', 'layout'];

    protected $casts = ['layout' => 'array'];
}
