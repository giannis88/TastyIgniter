<?php

namespace System\Classes;

use System\Interfaces\HasSolutionsForThrowable;
use System\Classes\Solution;
use Throwable;
use Log;
use Illuminate\Container\Container;

class SolutionProviderRepository
{
    protected Container $container;
    protected array $solutions = [];
    protected array $solutionProviders = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Throwable $throwable
     * @return array
     */
    public function getSolutionsFor(Throwable $throwable): array
    {
        $solutions = collect($this->solutionProviders)
            ->filter(function (HasSolutionsForThrowable $solutionProvider) use ($throwable) {
                try {
                    return $solutionProvider->canSolve($throwable);
                } catch (Throwable $e) {
                    return false;
                }
            })
            ->map(function (HasSolutionsForThrowable $solutionProvider) use ($throwable) {
                try {
                    return $solutionProvider->getSolutions($throwable);
                } catch (Throwable $e) {
                    return [];
                }
            })
            ->flatten()
            ->toArray();

        $providedSolutions = $this->solutions[$throwable::class] ?? [];
        return array_merge($solutions, $providedSolutions);
    }

    /**
     * @param string $solutionClass
     * @return Solution|null
     */
    public function getSolutionForClass(string $solutionClass): ?Solution
    {
        return $this->resolveSolution($solutionClass);
    }

    /**
     * Register a solution provider.
     *
     * @param HasSolutionsForThrowable $provider
     * @return void
     */
    public function registerSolutionProvider(HasSolutionsForThrowable $provider): void
    {
        $this->solutionProviders[] = $provider;
    }

    /**
     * Register a solution for a specific throwable class.
     *
     * @param string $throwableClass
     * @param Solution $solution
     * @return void
     */
    public function registerSolution(string $throwableClass, Solution $solution): void
    {
        if (!isset($this->solutions[$throwableClass])) {
            $this->solutions[$throwableClass] = [];
        }
        
        $this->solutions[$throwableClass][] = $solution;
    }

    /**
     * Clear all registered solutions.
     *
     * @return void
     */
    public function clearSolutions(): void
    {
        $this->solutions = [];
        $this->solutionProviders = [];
    }

    /**
     * Resolve a solution provider instance from a class name.
     *
     * @param string|null $solutionClass
     * @return \App\Contracts\Solution|null
     */
    protected function resolveSolution(?string $solutionClass): ?Solution
    {
        if (empty($solutionClass) || ! class_exists($solutionClass)) {
            return null;
        }

        if (! in_array(Solution::class, class_implements($solutionClass) ?: [])) {
            return null;
        }

        try {
            return $this->container->make($solutionClass);
        } catch (Throwable $e) {
            Log::warning("Failed to resolve solution: {$solutionClass}", [
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }
}
