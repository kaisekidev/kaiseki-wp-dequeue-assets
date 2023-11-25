<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\DequeueAssets;

use Kaiseki\WordPress\Context\Filter\ContextFilterInterface;
use Kaiseki\WordPress\Hook\HookCallbackProviderInterface;

use function is_array;
use function is_callable;

/**
 * @phpstan-type DequeueCondition bool|ContextFilterInterface
 * @phpstan-type DequeueConfig array{
 *      action?: string,
 *      dequeue?: DequeueCondition,
 *      priority?: int,
 * }
 * @phpstan-type DequeueCallback callable(string $handle): void
 */
final class DequeueAssets implements HookCallbackProviderInterface
{
    /** @var array<string, array<string, DequeueConfig>|DequeueCondition> $defaultHookConfigs */
    public array $defaultHookConfigs;
    /** @var array<string, array<string, DequeueConfig>|DequeueCondition> $customHookConfigs */
    public array $customHookConfigs;

    /**
     * @param array<string, DequeueCondition|DequeueConfig> $scripts
     * @param array<string, DequeueCondition|DequeueConfig> $styles
     */
    public function __construct(
        private readonly array $scripts,
        private readonly array $styles,
    ) {
    }

    public function registerHookCallbacks(): void
    {
        [$defaultScriptDequeues, $customScriptDequeues] = $this->getConfigs($this->scripts);
        $this->registerDefaultHooks($defaultScriptDequeues, [$this, 'dequeueScript']);
        $this->registerCustomHooks($customScriptDequeues, [$this, 'dequeueScript']);
        [$defaultStyleDequeues, $customStyleDequeues] = $this->getConfigs($this->styles);
        $this->registerDefaultHooks($defaultStyleDequeues, [$this, 'dequeueStyle']);
        $this->registerCustomHooks($customStyleDequeues, [$this, 'dequeueStyle']);
    }

    /**
     * @param array<string, DequeueCondition|DequeueConfig> $configs
     *
     * @return array{array<string, DequeueCondition>, array<string, DequeueConfig>}
     */
    private function getConfigs(array $configs): array
    {
        $defaultHookConfigs = [];
        $customHookConfigs = [];
        foreach ($configs as $handle => $config) {
            if ($config === true || $config instanceof ContextFilterInterface) {
                $defaultHookConfigs[$handle] = $config;
                continue;
            }
            if (!is_array($config) || $config === []) {
                continue;
            }
            $customHookConfigs[$handle] = $config;
        }
        return [$defaultHookConfigs, $customHookConfigs];
    }

    /**
     * @param array<string, DequeueCondition> $configs
     * @param DequeueCallback                 $dequeueCallback
     */
    private function registerDefaultHooks(array $configs, callable $dequeueCallback): void
    {
        foreach ($configs as $handle => $condition) {
            if (
                !($condition instanceof ContextFilterInterface && $condition())
                && $condition !== true
            ) {
                continue;
            }
            add_action(
                'wp_enqueue_scripts',
                fn() => $dequeueCallback($handle),
                11
            );
        }
    }

    /**
     * @param array<string, DequeueConfig> $configs
     * @param DequeueCallback              $dequeueCallback
     */
    private function registerCustomHooks(array $configs, callable $dequeueCallback): void
    {
        foreach ($configs as $handle => $config) {
            $action = $config['action'] ?? 'wp_enqueue_scripts';
            $priority = $config['priority'] ?? 11;
            if (!isset($config['dequeue'])) {
                $this->registerCustomHook($dequeueCallback, $handle, $action, $priority);
                continue;
            }
            if (is_callable($config['dequeue']) && $config['dequeue']()) {
                $this->registerCustomHook($dequeueCallback, $handle, $action, $priority);
                continue;
            }
            if ($config['dequeue'] !== true) {
                continue;
            }
            $this->registerCustomHook($dequeueCallback, $handle, $action, $priority);
        }
    }

    /**
     * @param DequeueCallback $dequeueCallback
     * @param string          $handle
     * @param string          $action
     * @param int             $priority
     */
    private function registerCustomHook(callable $dequeueCallback, string $handle, string $action, int $priority): void
    {
        add_action(
            $action,
            fn() => $dequeueCallback($handle),
            $priority
        );
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
