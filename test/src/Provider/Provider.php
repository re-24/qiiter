<?php

namespace re24\Qiiter\Test\Provider;

class Provider
{
    public static function getProviderData($path)
    {
        $dir = __DIR__;
        return file_get_contents($dir.'/'.$path);
    }
}
