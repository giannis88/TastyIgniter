<?php

namespace System\Contracts;

interface PrioritizedProvider
{
    /**
     * Get the priority level of the service provider.
     * Higher numbers load first.
     */
    public function getPriority(): int;

    /**
     * Get provider dependencies.
     */
    public function getDependencies(): array;
}
