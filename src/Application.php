<?php

namespace Fenrir\Framework;

use DI\Container;
use DI\ContainerBuilder;
use Dotenv\Dotenv;

class Application
{

    public Container $container;
    private Router $router;

    /**
     * Summary of plugins
     * @var array<PluginInterface>
     */
    private array $plugins = [];

    public function __construct(
        public string $root_path
    ) {
        $dotenv  = Dotenv::createMutable($this->root_path);
        $dotenv->load();

        $builder = new ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/config/container-definitions.php');

        foreach ($this->plugins as $plugin) {
            $definitions_file = $plugin->getPath() . '/config/container-definitions.php';
            if (file_exists($definitions_file)) {
                $builder->addDefinitions($definitions_file);
            }
        }

        $local_definitions_file = $this->root_path . '/config/container-definitions.php';
        if (file_exists($local_definitions_file)) {
            $builder->addDefinitions($local_definitions_file);
        }

        $builder->useAttributes(true);
        $builder->useAutowiring(true);
        $this->container = $builder->build();
        $this->container->set(Application::class, $this);
        $this->router = $this->container->get(Router::class);
    }
    public function run()
    {
        $this->router->route($this->root_path);
    }

    /**
     * Summary of use
     * @param class-string $plugin
     * @return void
     */
    public function use(string $plugin)
    {
        $this->plugins[] = $this->container->get($plugin);
    }

    /**
     * Summary of getPlugins
     * @return array<PluginInterface>
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }
}
