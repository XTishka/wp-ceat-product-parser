<?php
declare(strict_types=1);

namespace CeatProductParser\Contracts;

interface ServiceProviderInterface
{
    public function register(): void;
}
