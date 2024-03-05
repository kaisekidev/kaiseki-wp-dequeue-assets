<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\DequeueAssets;

final class ConfigProvider
{
    /**
     * @return array<mixed>
     */
    public function __invoke(): array
    {
        return [
            'dequeue_assets' => [
                'scripts' => [],
                'styles' => [],
            ],
            'hook' => [
                'provider' => [
                    DequeueAssets::class,
                ],
            ],
            'dependencies' => [
                'aliases' => [],
                'factories' => [
                    DequeueAssets::class => DequeueAssetsFactory::class,
                ],
            ],
        ];
    }
}
