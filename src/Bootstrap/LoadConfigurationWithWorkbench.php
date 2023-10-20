<?php

namespace Orchestra\Testbench\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use Orchestra\Testbench\Workbench\Workbench;
use Symfony\Component\Finder\Finder;
use function Orchestra\Testbench\workbench_path;

/**
 * @internal
 */
class LoadConfigurationWithWorkbench extends LoadConfiguration
{
    /**
     * Determine if workbench config file should be loaded.
     *
     * @var bool
     */
    protected $usesWorkbenchConfigFile = false;

    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app): void
    {
        $this->usesWorkbenchConfigFile = (Workbench::configuration()->getWorkbenchDiscoversAttributes()['config'] ?? false)
            && is_dir(workbench_path('config'));

        parent::bootstrap($app);
    }

    /**
     * Resolve the configuration file.
     *
     * @param  string  $path
     * @param  string  $key
     * @return string
     */
    protected function resolveConfigurationFile(string $path, string $key): string
    {
        return $this->usesWorkbenchConfigFile === true && is_file(workbench_path("config/{$key}.php"))
            ? workbench_path("config/{$key}.php")
            : $path;
    }

    /**
     * Extend the loaded configuration.
     *
     * @param  \Illuminate\Support\Enumerable  $configurations
     * @return void
     */
    protected function extendsLoadedConfiguration(Enumerable $configurations): void
    {
        if ($this->usesWorkbenchConfigFile === false) {
            return;
        }

        $workbenchConfigurations = LazyCollection::make(static function () {
            if (is_dir(workbench_path('config'))) {
                foreach (Finder::create()->files()->name('*.php')->in(workbench_path('config')) as $file) {
                    yield basename($file->getRealPath(), '.php') => $file->getRealPath();
                }
            }
        })->reject(static function ($path, $key) use ($configurations) {
            return $configurations->has($key);
        })->each(static function ($path, $key) use ($configurations) {
            $configurations->put($key, $path);
        });
    }
}
