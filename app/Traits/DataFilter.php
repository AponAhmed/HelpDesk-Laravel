<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait DataFilter
{
    public $MAX_IN_PAGE = 15;

    /**
     * @param Builder $data
     */
    function customFilter($data)
    {
        $request = request();
        if ($request->has('filter') && $request->filter != "") {
            $filterWith = $request->get('filter');
            if ($request->has('val')) {
                $filterVal = $request->get('val');
                if ($filterVal !== "") {
                    $data->where($filterWith, '=', $filterVal);
                }
            }
        }

        return $data;
    }

    /**
     * @param Builder $builder
     * @param Array $filds To Search
     * @param String $q Search Query string
     * @return Builder
     */
    function customSearch(Builder $builder, array $fields)
    {
        $request = request();
        $searchQ = $request->get('q');

        if ($searchQ == "") {
            return $builder;
        }

        $i = 0;
        foreach ($fields as $fild) {
            if ($i == 0) {
                $builder->where($fild, 'LIKE', "%{$searchQ}%");
            } else {
                $builder->orWhere($fild, 'LIKE', "%{$searchQ}%");
            }
            $i++;
        }
        return $builder;
    }

    /**
     * @param Builder $data;
     */
    function addExtraData($data)
    {
        $request = request();
        $searchQ = $request->get('q');
        //Filter Data
        //Append Aditional Data with Response
        if ($request->has('filter') && $request->filter != "") {
            $filterWith = $request->get('filter');
            if ($request->has('val')) {
                $filterVal = $request->get('val');
                $filter = ['filter' => $filterWith, 'val' => $filterVal];
                $data->appends($filter);
                $custom = collect(['filter' => $filter]);
                $data = $custom->merge($data);
            }
        }
        //Search Query
        if ($request->has('q')) {
            $data->appends(['q' => $searchQ]);
        }
        return $data;
    }

    function itemPerPage()
    {
        if (session('itemPerPage')) {
            $this->MAX_IN_PAGE = session('itemPerPage');
        }
    }
}
