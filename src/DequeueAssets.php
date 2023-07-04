<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\DequeueAssets;

use Kaiseki\WordPress\Hook\HookCallbackProviderInterface;

use function is_string;

/**
 * @phpstan-type DequeueCallback callable(): bool
 * @phpstan-type DequeueConfig array{
 *      dequeue?: DequeueCallback,
 *      action?: string,
 *      priority?: int,
 * }
 */
final class DequeueAssets implements HookCallbackProviderInterface
{
    /** @var array<string, array<string, DequeueConfig>> $defaultActionconfigs */
    public array $defaultActionconfigs;
    /** @var array<string, array<string, DequeueConfig>> $customActionConfigs */
    public array $customActionConfigs;

    /**
     * @param array<string, DequeueConfig> $scripts
     * @param array<string, DequeueConfig> $styles
     */
    public function __construct(
        array $scripts,
        array $styles,
    ) {
        $this->processConfigs('scripts', $scripts);
        $this->processConfigs('styles', $styles);
    }

    /**
     * @param string $type
     * @param array<string, DequeueConfig>  $configs
     *
     * @return void
     */
    private function processConfigs(string $type, array $configs): void
    {
        foreach ($configs as $handle => $config) {
            if (!isset($config['action'])) {
                continue;
            }
            if (!is_string($config['action'])) {
                continue;
            }
            if ($config['action'] === '') {
                continue;
            }
            $this->customActionConfigs[$type][$handle] = $config;
        }
    }

    public function registerHookCallbacks(): void
    {
        foreach ($this->customActionConfigs['scripts'] as $handle => $config) {
            if (!isset($config['action'])) {
                continue;
            }
            add_action($config['action'], fn() => $this->dequeueScript($handle), $config['priority'] ?? 11);
        }
        foreach ($this->customActionConfigs['styles'] as $handle => $config) {
            if (!isset($config['action'])) {
                continue;
            }
            add_action($config['action'], fn() => $this->dequeueStyle($handle), $config['priority'] ?? 11);
        }
        add_action('wp_enqueue_scripts', [$this, 'dequeueAssets'], 11);
    }

    public function dequeueAssets(): void
    {
        foreach ($this->defaultActionconfigs['scripts'] as $handle => $config) {
            $this->dequeueScript($handle);
        }
        foreach ($this->defaultActionconfigs['styles'] as $handle => $config) {
            $this->dequeueStyle($handle);
        }
    }

    private function dequeueScript(string $script): void
    {
        wp_deregister_script($script);
        wp_dequeue_script($script);
    }

    private function dequeueStyle(string $style): void
    {
        wp_deregister_style($style);
        wp_dequeue_style($style);
    }
}
