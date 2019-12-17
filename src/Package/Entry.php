<?php

namespace Anomaly\Streams\Platform\Webpack\Package;

use Illuminate\Support\Str;

class Entry
{
    /** @var Package */
    protected $package;

    /** @var string */
    protected $name;

    /** @var string|null */
    protected $suffix;

    /** @var string */
    protected $path;

    /** @var string */
    protected $provider;

    /** @var \Illuminate\Support\Collection|string[]|array */
    protected $scripts;

    /** @var \Illuminate\Support\Collection|string[]|array */
    protected $styles;

    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    public function renderScripts()
    {

        $scripts = $this->getScripts()->map(function ($script) {
            return $this->html->script($script);
        });
        return $scripts;
    }

    public function isMain()
    {
        return $this->package->getExportName() === $this->name;
    }

    public function isSuffixed()
    {
        return $this->suffix !== null;
    }

    public function matchSuffix($suffix)
    {
        if ($suffix === null) {
            return $this->suffix === null;
        }
        return Str::is($suffix, $this->suffix);
    }

    public function hasScripts()
    {
        return $this->scripts->isNotEmpty();
    }

    public function hasStyles()
    {
        return $this->styles->isNotEmpty();
    }

    public function hasProvider()
    {
        return $this->provider !== null;
    }

    // generated

    public function getPackage()
    {
        return $this->package;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function getScripts()
    {
        return $this->scripts;
    }

    public function getStyles()
    {
        return $this->styles;
    }

    public function getSorted()
    {
        return $this->package->getSorted();
    }
}
