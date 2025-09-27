<?php
declare(strict_types=1);

namespace CeatProductParser\Container;

use CeatProductParser\Contracts\ServiceProviderInterface;

final class Container
{
    /**
     * @var array<string, mixed>
     */
    private array $bindings = [];

    /**
     * @var ServiceProviderInterface[]
     */
    private array $providers = [];

    public function set(string $id, $value): void
    {
        $this->bindings[$id] = $value;
    }

    public function get(string $id, $default = null)
    {
        return $this->bindings[$id] ?? $default;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->bindings);
    }

    public function add_provider(ServiceProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * @return ServiceProviderInterface[]
     */
    public function providers(): array
    {
        return $this->providers;
    }
}
