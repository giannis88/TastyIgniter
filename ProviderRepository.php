<?php

namespace Igniter\Main;

use RuntimeException;
use ReflectionClass;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Foundation\ProviderRepository as BaseProviderRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;

class ProviderRepository extends BaseProviderRepository
{
    /**
     * Create a new service repository instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $manifestPath
     * @return void
     */
    public function __construct(Application $app, Filesystem $files, $manifestPath)
    {
        parent::__construct($app, $files, $manifestPath);
        $this->app = $app;
        $this->files = $files;
    }

    /**
     * The application implementation.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Create a new provider instance.
     *
     * @param  string  $provider
     * @return \Illuminate\Support\ServiceProvider
     */
    public function createProvider($provider): ServiceProvider
    {
        $cacheKey = 'provider.reflection.' . md5($provider);

        try {
            $validationResult = Cache::remember($cacheKey, 3600, function () use ($provider) {
                if (! class_exists($provider)) {
                    return ['valid' => false, 'error' => "Provider [{$provider}] not found."];
                }

                $reflectionClass = new ReflectionClass($provider);

                if (! $reflectionClass->isSubclassOf(ServiceProvider::class)) {
                    return ['valid' => false, 'error' => "Provider [{$provider}] must extend the ServiceProvider class."];
                }

                if (! $reflectionClass->isInstantiable()) {
                    return ['valid' => false, 'error' => "Provider [{$provider}] must be instantiable."];
                }

                return ['valid' => true, 'version' => $this->getProviderVersion($reflectionClass)];
            });

            if (! $validationResult['valid']) {
                Log::error("Provider validation failed: {$provider}", $validationResult);
                throw new RuntimeException($validationResult['error']);
            }

            return new $provider($this->app);
        } catch (\ReflectionException $e) {
            Log::error("Provider reflection failed: {$provider}", ['exception' => $e]);
            Cache::forget($cacheKey);
            throw new RuntimeException("Failed to analyze provider [{$provider}]: " . $e->getMessage());
        }
    }

    /**
     * Write the service manifest file to disk.
     *
     * @param  array  $manifest
     * @return array
     *
     * @throws \RuntimeException
     */
    protected function writeManifest(array $manifest)
    {
        try {
            $manifest = $this->validateManifest($manifest);
            $manifest['health'] = $this->checkProvidersHealth($manifest['providers']);
            $manifest['timestamp'] = time();

            $lockFile = $this->manifestPath . '.lock';
            if (!$this->acquireLock($lockFile)) {
                throw new RuntimeException('Unable to acquire manifest lock');
            }

            try {
                return parent::writeManifest($manifest);
            } finally {
                $this->releaseLock($lockFile);
            }
        } catch (\Exception $e) {
            Log::error('Failed to write manifest', ['exception' => $e]);
            throw new RuntimeException("Could not write manifest: {$e->getMessage()}");
        }
    }

    protected function acquireLock(string $lockFile): bool
    {
        $handle = fopen($lockFile, 'c+');
        if (!$handle) {
            return false;
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return false;
        }

        $this->lockHandle = $handle;
        return true;
    }

    protected function releaseLock(string $lockFile): void
    {
        if (isset($this->lockHandle)) {
            flock($this->lockHandle, LOCK_UN);
            fclose($this->lockHandle);
            @unlink($lockFile);
            unset($this->lockHandle);
        }
    }

    protected function validateManifest($manifest)
    {
        if (!is_array($manifest) || !isset($manifest['providers'])) {
            Log::error('Invalid manifest structure detected');
            throw new RuntimeException('The provider manifest is corrupted.');
        }

        $manifest['providers'] = $this->sortProviders($manifest['providers']);

        // Add cache busting hash
        $manifest['hash'] = md5(serialize($manifest['providers']));

        return array_merge([
            'dependencies' => [],
            'priorities' => [],
            'health' => [],
        ], $manifest);
    }

    protected function saveManifest(array $manifest)
    {
        // Implement the logic to save the manifest
        // This is a placeholder for the actual implementation
        // For example, you can save it to a file or a database
        return $manifest;
    }

    protected function checkProvidersHealth(array $providers)
    {
        return Collection::make($providers)
            ->mapWithKeys(function ($provider) {
                return [$provider => $this->isProviderHealthy($provider)];
            })->all();
    }

    protected function isProviderHealthy(string $provider): bool
    {
        try {
            $reflection = new ReflectionClass($provider);
            return $reflection->isInstantiable()
                && $reflection->isSubclassOf(ServiceProvider::class)
                && method_exists($provider, 'register');
        } catch (\Throwable $e) {
            Log::warning("Provider health check failed: {$provider}", ['exception' => $e]);
            return false;
        }
    }

    protected function getProviderVersion(ReflectionClass $reflection)
    {
        if ($reflection->hasConstant('VERSION')) {
            return $reflection->getConstant('VERSION');
        }

        return null;
    }

    protected function sortProviders(array $providers): array
    {
        $graph = $this->buildDependencyGraph($providers);
        $sorted = $this->topologicalSort($graph);

        return Collection::make($sorted)
            ->sortByDesc(function ($provider) {
                return $this->getProviderPriority($provider);
            })
            ->values()
            ->all();
    }

    protected function buildDependencyGraph(array $providers): array
    {
        $graph = [];
        foreach ($providers as $provider) {
            $graph[$provider] = $this->getProviderDependencies($provider);
        }

        $this->validateDependencyGraph($graph);
        return $graph;
    }

    protected function validateDependencyGraph(array $graph): void
    {
        foreach ($graph as $provider => $dependencies) {
            foreach ($dependencies as $dependency) {
                if (!isset($graph[$dependency])) {
                    Log::error("Missing provider dependency: {$dependency} required by {$provider}");
                    throw new RuntimeException("Provider dependency not found: {$dependency}");
                }
            }
        }
    }

    protected function topologicalSort(array $graph): array
    {
        $visited = [];
        $sorted = [];
        $visiting = [];

        foreach ($graph as $provider => $dependencies) {
            if (!isset($visited[$provider])) {
                $this->visit($provider, $graph, $visited, $visiting, $sorted);
            }
        }

        return array_reverse($sorted);
    }

    protected function visit(string $provider, array $graph, array &$visited, array &$visiting, array &$sorted): void
    {
        if (isset($visiting[$provider])) {
            $path = array_keys($visiting);
            $path[] = $provider;
            Log::error("Cyclic provider dependency detected", ['path' => $path]);
            throw new RuntimeException("Cyclic provider dependency detected: " . implode(' -> ', $path));
        }

        $visiting[$provider] = true;

        foreach ($graph[$provider] as $dependency) {
            if (!isset($visited[$dependency])) {
                $this->visit($dependency, $graph, $visited, $visiting, $sorted);
            }
        }

        unset($visiting[$provider]);
        $visited[$provider] = true;
        $sorted[] = $provider;
    }

    protected function getProviderPriority(string $provider): int
    {
        try {
            $instance = $this->createProvider($provider);
            return $instance instanceof \System\Contracts\PrioritizedProvider
                ? $instance->getPriority()
                : 0;
        } catch (\Throwable $e) {
            Log::warning("Could not determine provider priority: {$provider}");
            return 0;
        }
    }

    protected function getProviderDependencies(string $provider): array
    {
        try {
            $instance = $this->createProvider($provider);
            return $instance instanceof \System\Contracts\PrioritizedProvider
                ? $instance->getDependencies()
                : [];
        } catch (\Throwable $e) {
            Log::warning("Could not determine provider dependencies: {$provider}");
            return [];
        }
    }
}
