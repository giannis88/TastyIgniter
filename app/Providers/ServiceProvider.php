class ServiceProvider extends BaseServiceProvider
{
    // ...existing code...

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('log', function ($app) {
            return new \App\Logging\LoggerFactory();
        });
    }

    // ...existing code...
}
