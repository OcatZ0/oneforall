<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardLayout extends Model
{
    protected $table      = 'dashboard_layouts';
    public    $timestamps = false;

    protected $fillable = ['user_id', 'page', 'layout'];

    protected $casts = ['layout' => 'array'];
}
