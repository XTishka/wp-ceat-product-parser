<?php
declare(strict_types=1);

namespace CeatProductParser;

use CeatProductParser\Container\Container;
use CeatProductParser\Contracts\ServiceProviderInterface;
use CeatProductParser\Providers\AdminServiceProvider;
use CeatProductParser\Providers\CoreServiceProvider;
use CeatProductParser\Services\HtmlParser;

final class Plugin
{
    private static ?self $instance = null;

    private Container $container;

    private function __construct()
    {
        $this->container = new Container();
        $this->register_default_services();
    }

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function boot(): void
    {
        $this->register_provider(new CoreServiceProvider($this->container));
        $this->register_provider(new AdminServiceProvider($this->container));

        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init(): void
    {
        foreach ($this->container->providers() as $provider) {
            $provider->register();
        }
    }

    public static function activate(): void
    {
        do_action('ceat_product_parser/activate');
    }

    public static function deactivate(): void
    {
        do_action('ceat_product_parser/deactivate');
    }

    public static function uninstall(): void
    {
        do_action('ceat_product_parser/uninstall');
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function register_provider(ServiceProviderInterface $provider): void
    {
        $this->container->add_provider($provider);
    }

    private function register_default_services(): void
    {
        $this->container->set('plugin.file', CEAT_PP_PLUGIN_FILE);
        $this->container->set('plugin.dir', CEAT_PP_PLUGIN_DIR);
        $this->container->set('plugin.url', CEAT_PP_PLUGIN_URL);
        $this->container->set('plugin.version', CEAT_PP_VERSION);
        $this->container->set('service.html_parser', new HtmlParser());
    }
}
