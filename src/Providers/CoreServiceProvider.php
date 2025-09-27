<?php
declare(strict_types=1);

namespace CeatProductParser\Providers;

use CeatProductParser\Container\Container;
use CeatProductParser\Contracts\ServiceProviderInterface;

final class CoreServiceProvider implements ServiceProviderInterface
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register(): void
    {
        add_action('init', [$this, 'load_textdomain']);
    }

    public function load_textdomain(): void
    {
        load_plugin_textdomain(
            'ceat-product-parser',
            false,
            dirname(plugin_basename($this->container->get('plugin.file'))) . '/languages'
        );
    }
}
