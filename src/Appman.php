<?php

namespace Krakero\Appman;

class Appman
{
    protected $custom_data;

    public static function setCustomData($callback)
    {
        config(['appman.custom' => array_merge(config('appman.custom'), $callback())]);
    }
}
