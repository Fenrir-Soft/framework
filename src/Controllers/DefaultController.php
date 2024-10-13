<?php

namespace Fenrir\Framework\Controllers;

use Fenrir\Framework\Lib\Request;
use Fenrir\Framework\Lib\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class DefaultController
{
    public function __construct(
        public Request $request,
        public Response $response,
        public Environment $view
    ) {}

    #[Route(path: '/{controller}/{action}/{params}', requirements:["params" => '.*'], priority: -1000)]
    public function index($controller = 'home', $action = 'index', $params = '')
    {
        $this->request->attributes->set('controller', $controller);
        $this->request->attributes->set('action', $action);

        $params = explode('/', trim($params, '/'));
        $params = array_filter($params, function (string $part) {
            return $part !== '';
        });
        $this->request->attributes->set('params', $params);

        $tpl = "{$controller}/{$action}.html.twig";


        if (!$this->view->getLoader()->exists($tpl)) {
            $tpl = "{$controller}/index.html.twig";
        }

        if (!$this->view->getLoader()->exists($tpl)) {
            $tpl = "{$controller}.html.twig";
        }

        if (!$this->view->getLoader()->exists($tpl)) {
            $tpl = "home/{$action}.html.twig";
        }

        if (!$this->view->getLoader()->exists($tpl)) {
            $tpl = "home/index.html.twig";
        }

        if (!$this->view->getLoader()->exists($tpl)) {
            $tpl = "home.html.twig";
        }

        if (!$this->view->getLoader()->exists($tpl)) {
            $tpl = "index.html.twig";
        }

        $body = $this->view->render($tpl);
        $this->response->setContent($body);
    }
}
