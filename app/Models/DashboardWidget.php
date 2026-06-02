<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardWidget extends Model
{
    protected $fillable = ['dashboard_id', 'widget_id', 'instruction','title'];
}
