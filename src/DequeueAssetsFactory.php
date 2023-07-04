<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\DequeueAssets;

use Kaiseki\Config\Config;
use Psr\Container\ContainerInterface;

/**
 * @phpstan-import-type DequeueConfig from DequeueAssets
 */
class DequeueAssetsFactory
{
    public function __invoke(ContainerInterface $container): DequeueAssets
    {
        $config = Config::get($container);
        /** @var array<string, DequeueConfig> $scripts */
        $scripts = $config->array('dequeue_assets/scripts', []);
        /** @var array<string, DequeueConfig> $styles */
        $styles = $config->array('dequeue_assets/styles', []);
        return new DequeueAssets($scripts, $styles);
    }
}
