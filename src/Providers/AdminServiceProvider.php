<?php
declare(strict_types=1);

namespace CeatProductParser\Providers;

use CeatProductParser\Admin\ToolsPage;
use CeatProductParser\Container\Container;
use CeatProductParser\Contracts\ServiceProviderInterface;
use CeatProductParser\Services\HtmlParser;

final class AdminServiceProvider implements ServiceProviderInterface
{
    private Container $container;

    private ?ToolsPage $tools_page = null;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register(): void
    {
        $parser = $this->resolveParser();

        $this->tools_page = new ToolsPage($parser);

        add_action('admin_menu', [$this->tools_page, 'register']);
    }

    private function resolveParser(): HtmlParser
    {
        if ($this->container->has('service.html_parser')) {
            $parser = $this->container->get('service.html_parser');
            if ($parser instanceof HtmlParser) {
                return $parser;
            }
        }

        $parser = new HtmlParser();
        $this->container->set('service.html_parser', $parser);

        return $parser;
    }
}
