<?php

namespace Anomaly\Streams\Webpack;

use Collective\Html\HtmlBuilder;
use InvalidArgumentException;
use Anomaly\Streams\Webpack\Package\Entry;
use Anomaly\Streams\Webpack\Package\EntryCollection;

class Webpack
{
    //@formatter:off
    /** @var bool */
    //    protected $active;
    /** @var bool */
    protected $enabled;
    /** @var string */
    protected $path;
    /** @var string */
    protected $protocol;
    /** @var string */
    protected $host;
    /** @var string */
    protected $port;
    /** @var string */
    protected $namespace;
    /** @var string */
    protected $outputPath;
    //@formatter:on

    /** @var array|\Anomaly\Streams\Webpack\WebpackData = \Anomaly\Streams\Webpack\WebpackDataExample::data() */
    protected $data;

    /** @var \Anomaly\Streams\Webpack\Package\Package[]|\Anomaly\Streams\Webpack\Package\PackageCollection */
    protected $packages;

    /** @var \Anomaly\Streams\Webpack\Package\EntryCollection|\Anomaly\Streams\Webpack\Package\Entry[] */
    protected $enabledEntries;

    /** @var \Collective\Html\HtmlBuilder */
    private $html;

    public function __construct(WebpackData $data, HtmlBuilder $html)
    {
        $this->data           = $data;
        $this->enabledEntries = new EntryCollection();
        $this->html           = $html;
    }

    public function isServer()
    {
        return $this->data['server'];
    }

    public function getMode()
    {
        return $this->data['mode'];
    }

    public function isMode($name)
    {
        return $this->data['mode'] === $name;
    }

    public function getPublicPath(...$parts)
    {
        return path_join($this->data['output.publicPath'], ...$parts);
    }

    public function findPackage($name)
    {
        if ($entry = $this->getPackages()->findByName($name)) {
            return $entry;
        }
        if ($entry = $this->getPackages()->findByComposerName($name)) {
            return $entry;
        }
        if ($entry = $this->getPackages()->findByStreamNamespace($name)) {
            return $entry;
        }
        throw new InvalidArgumentException("Could not find webpack addon with name '{$name}'");
    }

    public function enableEntry($name, $suffix = null)
    {
        $package = $this->findPackage($name);
        $entries = $package->getEntries();
        $entry   = $suffix === null ? $entries->main() : $entries->suffix($suffix);
        $this->enabledEntries->add($entry);
        return $this;
    }

    public function getEnabledEntries()
    {
        return $this->enabledEntries;
    }

    public function renderDevServerAssets()
    {
        $lines = [];
        foreach ($this->getPackages() as $package) {
            foreach ($package->getEntries() as $entry) {

                foreach ($entry->getStyles() as $style) {
                    $style   = $this->getPublicPath($style);
                    $lines[] = "<link rel='stylesheet' type='text/css' href='{$style}'></link>";
                }
                foreach ($entry->getScripts() as $script) {
                    $script  = $this->getPublicPath($script);
                    $lines[] = "<script src='{$script}'></script>";
                }
            }
        }
        return implode("\n", $lines);
    }

    public function renderScripts()
    {
        $scripts = $this->enabledEntries->sorted()->map->getScripts()->flatten()->map(function ($script) {
            return $this->html->script($this->getPublicPath($script));
        })->cast('string')->implode(PHP_EOL);
        return $scripts;
    }

    public function renderStyles()
    {
        $scripts = $this->enabledEntries->sorted()->map->getStyles()->flatten()->map(function ($style) {
            return $this->html->style($this->getPublicPath($style));
        })->cast('string')->implode(PHP_EOL);
        return $scripts;
    }

    public function renderProviders()
    {
        $p         = $this->getEnabledEntries()->filter->hasProvider()->map->getProvider()->map(function ($provider, $exportName) {
            $namespace = $this->getNamespace();
            return "{$namespace}.{$exportName}.{$provider}";
        })->values()->implode(', ');
        $providers = "window['{$this->getNamespace()}'].providers = [{$p}];";
        $providers = "<script> {$providers} </script>";
        return $providers;
    }

    public function renderAliases()
    {
        $entries = $this->enabledEntries->sorted()->map(function (Entry $entry) {
            $packageName = $entry->getPackage()->getName();
            $name        = $entry->getName();
            if ($entry->isSuffixed()) {
                $name        .= $entry->getSuffix();
                $packageName .= $entry->getSuffix();
            }
            return "window['{$this->getNamespace()}']['{$packageName}'] = window['{$this->getNamespace()}'].{$name};";
        });
        $helpers = $entries->implode("\n");

        return $this->html->tag('script', $helpers);
    }

    // generated

    public function getPackages()
    {
        return $this->packages;
    }

    public function setPackages($packages)
    {
        $this->packages = $packages;
        return $this;
    }

    public function isActive()
    {
        return $this->enabled && $this->isServer();
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getProtocol()
    {
        return $this->protocol;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getOutputPath()
    {
        return $this->outputPath;
    }
}
