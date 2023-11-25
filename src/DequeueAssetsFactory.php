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
 * @phpstan-type ConfigDequeueCondition ContextFilterType|list<ContextFilterType>
 * @phpstan-type ConfigDequeueConfig array{
 *       action?: string,
 *       dequeue?: bool|ConfigDequeueCondition,
 *       priority?: int,
 *  }
 *
 * @phpstan-import-type DequeueCondition from DequeueAssets
 * @phpstan-import-type DequeueConfig from DequeueAssets
 */
class DequeueAssetsFactory
{
    public function __invoke(ContainerInterface $container): DequeueAssets
    {
        $config = Config::get($container);
        /** @var array<string, bool|ConfigDequeueCondition|ConfigDequeueConfig> $scripts */
        $scripts = $config->array('dequeue_assets/scripts', []);
        /** @var array<string, bool|ConfigDequeueCondition|ConfigDequeueConfig> $styles */
        $styles = $config->array('dequeue_assets/styles', []);
        return new DequeueAssets(
            $this->getConfigs($scripts, $container),
            $this->getConfigs($styles, $container)
        );
    }

    /**
     * @param array<string, bool|ConfigDequeueCondition|ConfigDequeueConfig> $configs
     * @param ContainerInterface                                             $container
     *
     * @return array<string, DequeueCondition|DequeueConfig>
     */
    private function getConfigs(array $configs, ContainerInterface $container): array
    {
        return array_map(function ($config) use ($container) {
            if (is_bool($config)) {
                return $config;
            }

            if (!isset($config['dequeue'])) {
                /** @var ConfigDequeueCondition $condition */
                $condition = $config;
                return $this->getPipeline($condition, $container);
            }

            if (is_bool($config['dequeue'])) {
                return $config;
            }

            return [
                // @phpstan-ignore-next-line
                ...$config,
                'dequeue' => $this->getPipeline($config['dequeue'], $container),
            ];
        }, $configs);
    }

    /**
     * @param ConfigDequeueCondition $condition
     * @param ContainerInterface     $container
     *
     * @return ContextFilterInterface
     */
    private function getPipeline(
        string|ContextFilterInterface|array $condition,
        ContainerInterface $container
    ): ContextFilterInterface {
        $map = is_array($condition) ? $condition : [$condition];
        return new ContextFilterPipeline(...Config::initClassMap($container, $map));
    }
}
