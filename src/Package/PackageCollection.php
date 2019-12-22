<?php

namespace Anomaly\Streams\Webpack\Package;

use Illuminate\Support\Collection;

class PackageCollection extends Collection
{
    /** @var Package[] */
    protected $items = [];

    /**
     * @param \Anomaly\Streams\Webpack\Package\Package $package
     *
     * @return $this|\Illuminate\Support\Collection
     */
    public function add($package)
    {
        $this->items[$package->getName()] = $package;
        return $this;
    }

    /**
     * @param $namespace
     *
     * @return Package
     */
    public function findByStreamNamespace($namespace)
    {
        foreach ($this->items as $item) {
            if ($item->hasAddon() && $item->getAddon()->getNamespace() === $namespace) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @param $name
     *
     * @return Package
     */
    public function findByName($name)
    {
        return $this->firstWhere('name', $name);
    }

    public function hasAddon()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->filter->hasAddon();
    }

    /** @return \Anomaly\Streams\Webpack\Package\Package */
    public function findByComposerName($name)
    {
        if ($name) {
            foreach ($this->items as $item) {
                if ($item->getComposerName() === $name) {
                    return $item;
                }
            }
        }
        return null;
    }

    public function toBase()
    {
        return new Collection($this->items);
    }
}
