<?php

namespace App\Foundation;

use Illuminate\Foundation\Application as BaseApplication;

class Application extends BaseApplication
{
    public function getCachedClassesPath()
    {
        return $this->bootstrapPath('cache/classes.php');
    }

    public function getCachedServicesPath()
    {
        return $this->bootstrapPath('cache/services.php');
    }

    public function getCachedPackagesPath()
    {
        return $this->bootstrapPath('cache/packages.php');
    }

    public function after()
    {
        // Method stub
    }

    public function setAppContext(string $context)
    {
        $this['context'] = $context;
    }
}
