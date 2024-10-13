<?php

namespace Fenrir\Framework\Controllers;

use Fenrir\Framework\Lib\Request;
use Fenrir\Framework\Lib\Response;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Attribute\Route;

class AssetsController
{
    public function __construct(
        private Request $request,
        private Response $response,
        private FileLocatorInterface $file_locator
    ) {}

    #[Route(path: '/assets/{path}', requirements: ['path' => '.+'])]
    public function index($path = '')
    {
        try {
            $r = $this->file_locator->locate('assets/' . $path);
            $mimeTypes = new MimeTypes();
            $mimeType = $mimeTypes->guessMimeType($r);

            if (preg_match('#\.css$#', $r, $m)) {
                $mimeType = "text/css";
            }
            
            $this->response->setContentType($mimeType);
            $this->response->setResource(fopen($r, 'r'));
           
        } catch (\Throwable $th) {
            $this->response->setStatusCode(Response::HTTP_NOT_FOUND, "File not found");            
        }
    }
}
