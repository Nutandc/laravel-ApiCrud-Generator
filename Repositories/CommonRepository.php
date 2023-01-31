<?php

namespace App\Repositories;


use Illuminate\Database\Eloquent\Model;

class CommonRepository extends Repository
{
    public function __construct(protected Model $model)
    {

    }

}
