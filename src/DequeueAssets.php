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
        if (is_admin()) {
            return;
        }
        add_action('wp_enqueue_scripts', [$this,  'dequeueAssets'], 11);
    }

    public function dequeueAssets(): void
    {
        foreach ($this->scripts as $handle => $condition) {
            if (!$this->shouldDequeue($condition)) {
                continue;
            }
            $this->dequeueScript($handle);
        }

        foreach ($this->styles as $handle => $condition) {
            if (!$this->shouldDequeue($condition)) {
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

    private function shouldDequeue(bool|ContextFilterInterface $condition): bool
    {
        return $condition === true
            || ($condition instanceof ContextFilterInterface && $condition() === true);
    }
}
