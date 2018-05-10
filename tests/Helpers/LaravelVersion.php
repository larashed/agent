<?php

namespace Larashed\Agent\Tests\Helpers;

class LaravelVersion
{
    public static function below($version = '5.3.0')
    {
        return version_compare(app()->version(), $version, '<');
    }
}
