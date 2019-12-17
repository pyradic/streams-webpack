<?php

namespace Anomaly\Streams\Platform\Webpack;

use Anomaly\Streams\Platform\Http\Middleware\ApplicationReady;
use Anomaly\Streams\Platform\View\Event\TemplateDataIsLoading;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;
use Anomaly\Streams\Platform\Webpack\Command\ResolvePackageAddons;
use Illuminate\Contracts\Support\DeferrableProvider;

/**
 * Class WebpackServiceProvider
 *
 * @link   http://pyrocms.com/
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class WebpackServiceProvider extends ServiceProvider implements DeferrableProvider
{

    /**
     * Register the package.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            config_path('webpack.php'),
            'webpack'
        );

        $this->registerWebpack();
    }

    /**
     * Register webpack.
     */
    protected function registerWebpack()
    {
        $this->app->singleton('webpack', function (Application $app) {
            $factory = WebpackFactory::make($app);
            $webpack = $factory->build($app['config']['webpack.path']);
            return $webpack;
        });

        $this->app->alias('webpack', Webpack::class);

        $this->app->events->listen(ApplicationReady::class, function () {
            dispatch_now(new ResolvePackageAddons());
        });

        $this->app->events->listen(TemplateDataIsLoading::class, function (TemplateDataIsLoading $event) {
            $event->getTemplate()->set('webpack', $this->app->webpack);
            $this->app->view->share('webpack', $this->app->webpack);
        });
    }

    /**
     * Boot the package.
     *
     * @param \Illuminate\View\Factory $views
     * @param \Anomaly\Streams\Platform\Application\Application $application
     */
    public function boot(Factory $views, \Anomaly\Streams\Platform\Application\Application $application)
    {
        $this->publishes([
            dirname(__DIR__) . '/resources/config/webpack.php' => config_path('webpack.php'),
            dirname(__DIR__) . '/resources/views'              => resource_path('views/vendor/webpack'),
        ]);

        $views->addNamespace(
            'webpack',
            [
                //                $application->getResourcesPath(
                //                    "webpack/views/"
                //                ),
                resource_path('views/vendor/webpack'),
                __DIR__ . '/../resources/views',
            ]
        );
    }

    /**
     * Return the provided services.
     * 
     * @return array
     */
    public function provides()
    {
        return [Webpack::class, 'webpack'];
    }
}
