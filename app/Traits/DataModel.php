<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Models\MetaData;

trait DataModel
{

    function  DefaultFilter()
    {
        $this->casts["created_at"] = "date:d/m/Y";
        //$this->casts["updated_at"] = "date:d/m/Y";
    }

    function meta()
    {
        $metaData = $this->hasMany(MetaData::class, 'data_id', 'id')->where('table', $this->table)->get();
        $data = [];
        foreach ($metaData as $meta) {
            $data[$meta->name] = $meta->value;
        }
        return $data;
    }
}
