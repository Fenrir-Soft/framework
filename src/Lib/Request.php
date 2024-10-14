<?php

namespace Fenrir\Framework\Lib;

use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Throwable;

class Request extends HttpRequest
{
    public static function createFromGlobals(): static
    {
        $base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';
        $_SERVER['SCRIPT_NAME'] = $base  . basename($_SERVER['SCRIPT_NAME']);
        $_SERVER['SCRIPT_FILENAME'] = $base . basename($_SERVER['SCRIPT_FILENAME']);

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
