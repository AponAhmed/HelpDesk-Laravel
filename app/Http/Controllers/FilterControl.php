<?php

namespace App\Http\Controllers;

use App\Models\ViewFilter;

use Illuminate\Http\Request;
use App\Models\MailList;

class FilterControl extends Controller
{

    static $fields = ['snippet', 'subject', 'body', 'headers'];

    static function apply($data, $department_id, $fields = [])
    {
        $filterdData = [];
        if (is_string($data)) {
            return self::vfilter($data, $department_id, true);
        }
        $applyFields = array_merge(self::$fields, $fields);
        foreach ($data as $key => $value) {
            if (key_exists($key, $applyFields)) {
                $filterdData[$key] = self::vfilter($value, $department_id, true);
            } else {
                $filterdData[$key] = $value;
            }
        }
        return $filterdData; //viewFilter($data, $mailList->getDepartment);
    }

    /**
     * View Filter
     */
    public static function vfilter($data, $department_id, $filter = 0)
    {
        if (_UR("Super Admin") || empty($data) || _UR("Admin")) {
            return $data;
        }

        if (is_object($data)) {
            return $data;
        }

        if (!$filter) {
            return $data;
        }
        $sensored = "*"; //"&lt;-&gt;";

        $vfilters = ViewFilter::where(["department" => $department_id])->get();
        if ($vfilters->count() > 0) {
            foreach ($vfilters as $vfilter) {
                //Remove Links
                $data = preg_replace('/(<a([^>]*))(href=[^\s]*)/is', "$1", $data); //remove href Attributes from a
                // Define the regex pattern to match <a> tags with href attribute
                $pattern = '/<a\b([^>]*)\s*(?:href=[\'"][^\'"]*[\'"])?([^>]*)>(.*?)<\/a>/is';
                // Replace <a> tags with <div> tags and remove href attributes
                $replacement = '<span$1$2>$3</span>';
                $data = preg_replace($pattern, $replacement, $data);


                if ($vfilter->role == "email") {
                    $re = '/(?:[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/m';
                    $data = preg_replace($re, $sensored, $data);
                }
                if ($vfilter->role == "web") {
                    $re =
                        "/<[^<>]*>(*SKIP)(*F)|" .
                        "(w\s*w\s*w(\.|\s)*)+\w+(\.\w+|\-\w+)*\.[a-zA-Z]{2,5}" .
                        "/iu";
                    $data = preg_replace($re, $sensored, $data);
                }
                if ($vfilter->role == "word" || $vfilter->role == "mobile") {
                    $vfilter_key = [];
                    $vfilter_key = explode("\n", trim($vfilter->keys));
                    if (count($vfilter_key) > 0) {
                        foreach ($vfilter_key as $vfkey) {
                            $vfkey = @preg_quote(trim($vfkey));
                            if ($vfilter->role == "word") {
                                $re =
                                    "/<[^<>]*>(*SKIP)(*F)|" .
                                    "(\b" .
                                    $vfkey .
                                    ")\s*[a-z0-9\.\-\_\@]{3,}" .
                                    "/iu";
                                $data = preg_replace($re, '$1 ' . $sensored, $data);
                            } elseif ($vfilter->role == "mobile") {
                                $re =
                                    "/<[^<>]*>(*SKIP)(*F)|" .
                                    "(\b" .
                                    $vfkey .
                                    ")(\s|\&nbsp;|<[^<>]*>)*([0-9\s\(\)\-\+]{2,}\,?)+" .
                                    "/iu";
                                $data = @preg_replace($re, '$1 $2' . $sensored, $data);
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }
}
