<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repository extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'branch',
        'token',
        'job',
    ];
    
    public function deployments()
    {
        return $this->hasMany('App\Deployments', 'repository');
    }
}
