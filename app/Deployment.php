<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deployment extends Model
{
    protected $fillable = [
        'repository',
        'status',
        'request',
        'return_value',
        'output',
    ];

    public function repo()
    {
        return $this->hasOne('App\Repository', 'id', 'repository');
    }
}
