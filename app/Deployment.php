<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deployment extends Model
{
    protected $fillable = [
        'repository',
        'status',
        'request',
    ];

    public function repo()
    {
        $this->belongsTo('App\Repository', 'repository');
    }
}
