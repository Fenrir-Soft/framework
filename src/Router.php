<?php

namespace Fenrir\Framework;

use DI\Container;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\FileLocator;

use Fenrir\Framework\Lib\Request;
use Fenrir\Framework\Lib\Response;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Throwable;

class Router
{
    public function __construct(
        private Container $container,
        private Request $request,
        private Response $response,
        private MiddlewareCollection $middlewares,
        private Application $app
    ) {}
    private function getRoutes(string $root_path)
    {
        $dirs = [rtrim($root_path, '/') . '/src', __DIR__];

        $plugins = $this->app->getPlugins();
        foreach ($plugins as $plugin) {
            $dirs[] = $plugin->getPath();
        }

        $file_locator = new FileLocator($dirs);
        $controller_loader = new AttributeRouteControllerLoader();
        $loader = new AttributeDirectoryLoader($file_locator, $controller_loader);

        $routes2 = $loader->load($root_path . '/src');
        $routes = $loader->load(__DIR__);
        $routes->addCollection($routes2);

        foreach ($plugins as $plugin) {
            $routes_plugin = $loader->load($plugin->getPath());
            $routes->addCollection($routes_plugin);
        }

        return $routes;
    }
    public function route(string $root_path)
    {
        try {

            $routes = $this->getRoutes($root_path);


            $context = (new RequestContext())->fromRequest($this->request);
            $matcher = new UrlMatcher($routes, $context);

            try {
                $parameters = $matcher->matchRequest($this->request);
            } catch (NoConfigurationException $th) {
                throw new NotFoundHttpException(sprintf('Unable to find the controller for path "%s". The route is wrongly configured.', $this->request->getPathInfo()));
            }
            $this->request->attributes->replace($parameters);

            $controller_resolver = new ControllerResolver($this->container);
            $argument_resolver = new ArgumentResolver();

            $controller = $controller_resolver->getController($this->request);
            $arguments = $argument_resolver->getArguments($this->request, $controller);

            $this->setAttributes();

            $action = function () use ($controller, $arguments) {
                $this->container->call($controller, $arguments);
            };


            

            foreach ($this->middlewares as $middleware) {
                $action = function () use ($middleware, $action) {
                    $this->container->call(array($middleware, 'execute'), array($action));
                };
            }

            $action();
        } catch (NotFoundHttpException $th) {
            $this->response->setStatusCode(Response::HTTP_NOT_FOUND, $th->getMessage());
        } catch (ResourceNotFoundException $th) {
            $this->response->setStatusCode(Response::HTTP_NOT_FOUND, $th->getMessage());
        } catch (Throwable $th) {
            $this->response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, $th->getMessage());
        }
        $this->response->send();
    }

    public function setAttributes()
    {
        $info = new ReflectionMethod($this->request->attributes->get('_controller'));
        $class = $info->getDeclaringClass();

        // class attributes
        foreach ($class->getAttributes() as $attribute) {
            $name = basename(str_replace('\\', '/', $attribute->getName()));
            $this->request->attributes->set($name, $attribute->newInstance());
        }
        // method attributes
        foreach ($info->getAttributes() as $attribute) {
            $name = basename(str_replace('\\', '/', $attribute->getName()));
            $this->request->attributes->set($name, $attribute->newInstance());
        }
    }
}
