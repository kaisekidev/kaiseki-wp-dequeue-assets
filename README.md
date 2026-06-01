# kaiseki/wp-dequeue-assets

Conditionally dequeue and deregister WordPress scripts and styles using context filters.

A `kaiseki/wp-hook` `HookProviderInterface` wired through `ConfigProvider`: list the script/style
handles you want gone and, optionally, the condition under which to remove them. A condition is either
`true` (always, on the front end) or a `kaiseki/wp-context` `ContextFilterInterface` — given as an
instance, a class-string resolved from the container, or a list of filters combined into a
`ContextFilterPipeline` — so you can scope removal to specific templates, post types, etc.

## Installation

```bash
composer require kaiseki/wp-dequeue-assets
```

Requires PHP 8.2 or newer.

## Usage

Register `ConfigProvider` with your laminas-style config aggregator and configure the
`dequeue_assets` key:

```php
use Kaiseki\WordPress\DequeueAssets\DequeueAssets;

return [
    'dequeue_assets' => [
        'scripts' => [
            // handle => true (always) | ContextFilter class-string | list of filters
            'wp-embed'            => true,
            'comment-reply'       => true,
            'some-plugin-script'  => IsFrontPage::class,
        ],
        'styles' => [
            'wp-block-library'    => true,
        ],
    ],
    'hook' => [
        'provider' => [
            DequeueAssets::class,
        ],
    ],
];
```

`ConfigProvider` registers `DequeueAssetsFactory`, which reads the `dequeue_assets` config and resolves
each context filter (or list of filters, combined into a `ContextFilterPipeline`) from the container.
Removal runs on `wp_enqueue_scripts` and `wp_footer` (priority 999) and is skipped in the admin.

## Development

```bash
composer install
composer check   # check-deps, cs-check, phpstan
```

## License

MIT — see [LICENSE](LICENSE).
