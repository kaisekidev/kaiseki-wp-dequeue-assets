<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\DequeueAssets;

use Kaiseki\WordPress\Context\Filter\ContextFilterInterface;
use Kaiseki\WordPress\Hook\HookCallbackProviderInterface;

final class DequeueAssets implements HookCallbackProviderInterface
{
    /**
     * @param array<string, bool|ContextFilterInterface> $scripts
     * @param array<string, bool|ContextFilterInterface> $styles
     */
    public function __construct(
        private readonly array $scripts = [],
        private readonly array $styles = [],
    ) {
    }

    public function registerHookCallbacks(): void
    {

        add_action('wp_enqueue_scripts', [$this,  'dequeueAssets'], 11);
    }

    public function dequeueAssets(): void
    {
        foreach ($this->scripts as $handle => $condition) {
            if ($this->checkCondition($condition)) {
                continue;
            }
            $this->dequeueScript($handle);
        }

        foreach ($this->styles as $handle => $condition) {
            if ($this->checkCondition($condition)) {
                continue;
            }
            $this->dequeueStyle($handle);
        }
    }

    protected function dequeueScript(string $handle): void
    {
        wp_deregister_script($handle);
        wp_dequeue_script($handle);
    }

    protected function dequeueStyle(string $handle): void
    {
        wp_deregister_style($handle);
        wp_dequeue_style($handle);
    }

    private function checkCondition(bool|ContextFilterInterface $condition): bool
    {
        if ($condition === true) {
            return true;
        }
        if ($condition instanceof ContextFilterInterface) {
            return $condition() !== true;
        }
        return false;
    }
}
