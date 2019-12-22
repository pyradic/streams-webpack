<?php

namespace Anomaly\Streams\Webpack;

use GeneratedHydrator\Configuration;
use Illuminate\Contracts\Foundation\Application;
use Laradic\Support\Dot;
use Anomaly\Streams\Webpack\Package\Entry;
use Anomaly\Streams\Webpack\Package\EntryCollection;
use Anomaly\Streams\Webpack\Package\Package;
use Anomaly\Streams\Webpack\Package\PackageCollection;

class WebpackFactory
{
    /** @var Hydrator[] */
    protected $hydrators;

    /** @var array|\Anomaly\Streams\Webpack\WebpackData = \Anomaly\Streams\Webpack\WebpackDataExample::data() */
    private $data;

    /** @var \Illuminate\Contracts\Foundation\Application */
    private $app;

    /** @var \Anomaly\Streams\Webpack\Webpack */
    private $webpack;

    /** @var PackageCollection */
    private $packages;

    public function __construct(Application $app)
    {
        $this->app                    = $app;
        $this->hydrators              = [];
        $this->hydrators['webpack'] = $this->createHydrator(Webpack::class);
        $this->hydrators['package'] = $this->createHydrator(Package::class);
        $this->hydrators['entry']   = $this->createHydrator(Entry::class);
    }

    protected function createHydrator($class)
    {
        $configuration = new Configuration($class);
        $hydratorClass = $configuration->createFactory()->getHydratorClass();
        $hydrator      = new $hydratorClass;
        return $hydrator;
    }

    public static function make(?Application $app = null)
    {
        return new static($app ?: app());
    }

    public function build(string $path = null)
    {
        $this->buildData($path);
        $this->buildWebpack();
        $this->buildPackages();

        return $this->webpack;
    }

    protected function buildData(string $path = null)
    {
        $path = $path ?: $this->app->config->get('webpack.path', 'storage/webpack.json');
        $path = path_is_relative($path) ? base_path($path) : $path;
        $json = file_get_contents($path);
        $data = json_decode($json, true);

        $this->data = new WebpackData($data);
    }

    protected function buildWebpack()
    {
        $this->webpack = new Webpack($this->data, $this->app->html);
        $this->webpack->setPackages($this->packages = new PackageCollection);

        $this->hydrators['webpack']->hydrate(
            $this->app->config->get('webpack', []),
            $this->webpack
        );
    }

    protected function buildPackages()
    {
        foreach ($this->data['addons'] as $addon) {
            $package = $this->buildPackage($addon);
            foreach ($addon['entries'] as $name => $data) {
                $data['name'] = $name;
                $package->getEntries()->add(
                    $this->buildEntry($package, $data)
                );
            }
            $this->packages->add($package);
        }
    }

    /**
     * @param array|Dot $data = \Anomaly\Streams\Webpack\WebpackDataExample::addonDot()
     *
     * @return \Anomaly\Streams\Webpack\Package\Package
     */
    protected function buildPackage($data)
    {

        $package = new Package($this->webpack);
        $this->hydrators['package']->hydrate(
            $data->collect()->except(['entries'])->toArray(),
            $package
        );
        $package->setEntries(new EntryCollection);
        return $package;
    }

    /**
     * @param string    $name
     * @param array|Dot $data = \Anomaly\Streams\Webpack\WebpackDataExample::entry()
     *
     * @return \Anomaly\Streams\Webpack\Package\Entry
     */
    protected function buildEntry(Package $package, $data)
    {
        $entry = new Entry($package);
        $this->hydrators['entry']->hydrate(
            $data->toArray(),
            $entry
        );
        return $entry;
    }

    /**
     * @param \Anomaly\Streams\Webpack\WebpackData|array $addon = \Anomaly\Streams\Webpack\WebpackDataExample::addonDot()
     */
    protected function buildAddon($addon)
    { }
}
