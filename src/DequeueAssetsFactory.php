<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\DequeueAssets;

use Kaiseki\Config\Config;
use Kaiseki\WordPress\Context\Filter\ContextFilterInterface;
use Kaiseki\WordPress\Context\Filter\ContextFilterPipeline;
use Psr\Container\ContainerInterface;

use function array_map;
use function is_array;
use function is_bool;

/**
 * @phpstan-type ContextFilterType class-string<ContextFilterInterface>|ContextFilterInterface
 * @phpstan-type ContextFilterTypes ContextFilterType|list<ContextFilterType>
 */
class DequeueAssetsFactory
{
    public function __invoke(ContainerInterface $container): DequeueAssets
    {
        $config = Config::get($container);
        /** @var array<string, bool|ContextFilterTypes> $scripts */
        $scripts = $config->array('dequeue_assets/scripts', []);
        /** @var array<string, bool|ContextFilterTypes> $styles */
        $styles = $config->array('dequeue_assets/styles', []);
        return new DequeueAssets(
            $this->getConfigs($scripts, $container),
            $this->getConfigs($styles, $container)
        );
    }

    /**
     * @param array<string, bool|ContextFilterTypes> $configs
     * @param ContainerInterface                     $container
     *
     * @return array<string, bool|ContextFilterInterface>
     */
    private function getConfigs(array $configs, ContainerInterface $container): array
    {
        return array_map(function ($config) use ($container) {
            if (is_bool($config)) {
                return $config;
            }

            $map = is_array($config) ? $config : [$config];
            return new ContextFilterPipeline(...Config::initClassMap($container, $map));
        }, $configs);
    }
}
