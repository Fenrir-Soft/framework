<?php

use DI\Container;
use Fenrir\Framework\ValueObjects\RootDir;
use Fenrir\Framework\Application;
use Fenrir\Framework\Lib\Request;
use Fenrir\Framework\MiddlewareCollection;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

return [
    'twig' => [],
    RootDir::class => function (Application $app) {
        return new SplFileInfo($app->root_path);
    },
    FileLocatorInterface::class => function (Application $app) {
        $paths = [
            $app->root_path . '/src',
            dirname(__DIR__)
        ];

        $plugins = $app->getPlugins();
        foreach ($plugins as $plugin) {
            $paths[] = $plugin->getPath();
        }

        return new FileLocator($paths);
    },
    MiddlewareCollection::class => function () {
        return new MiddlewareCollection();
    },
    LoaderInterface::class => function (Application $app) {

        $paths = [];

        if (file_exists($app->root_path . '/src/views')) {
            $paths[] = $app->root_path . '/src/views';
        }

        $plugins = $app->getPlugins();
        foreach ($plugins as $plugin) {
            $path = $plugin->getPath() . '/views';
            if (file_exists($path)) {
                $paths[] = $path;
            }
        }

        $views_folder = dirname(__DIR__) . '/views';
        if (file_exists($views_folder)) {
            $paths[] = dirname(__DIR__) . '/views';
        }


        return new FilesystemLoader($paths);
    },
    Environment::class => function (
        LoaderInterface $loader,
        Container $container,
        Request $request
    ) {
        $options = $container->get('twig');

        $twig = new Environment($loader, $options);
        $twig->addGlobal('request', $request);

        return $twig;
    },
    Request::class => function () {
        return Request::createFromGlobals();
    },
];
