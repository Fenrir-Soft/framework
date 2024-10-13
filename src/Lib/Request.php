<?php

namespace Fenrir\Framework\Lib;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Throwable;

class Request extends HttpRequest
{
    public static function createFromGlobals(): static
    {
        if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '') {
            $_SERVER['REQUEST_URI'] = rtrim($_SERVER['REQUEST_URI'], '/');
        }
        
        $base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';
        $_SERVER['REQUEST_URI'] = str_replace(rtrim($base, '/'), '', $_SERVER['REQUEST_URI']);
        
        $request =  parent::createFromGlobals();
        try {
            if ($request->getContentTypeFormat() == 'json') {
                $data = $request->getPayload();
                $request->request = $data;
            }
        } catch (Throwable $e) {

        }  


        return $request;
    }
}
