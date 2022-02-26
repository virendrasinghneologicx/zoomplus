<?php

namespace YOOtheme\Theme;

use YOOtheme\Http\Request;
use YOOtheme\Http\Response;

class SettingsController
{
    public static function import(Request $request, Response $response, Updater $updater)
    {
        $config = $request('config');

        return $response->withJson($updater->update($config, []));
    }
}
