<?php

namespace Anomaly\Streams\Webpack\Package;


use Illuminate\Support\Collection;

class EntryCollection extends \Illuminate\Support\Collection
{
    /** @var Entry[] */
    protected $items = [];

    /**
     * @param \Anomaly\Streams\Webpack\Package\Entry $entry
     *
     * @return $this
     */
    public function add($entry)
    {
        $this->items[$entry->getName()] = $entry;
        return $this;
    }

    /**
     * @return Entry
     */
    public function main()
    {
        foreach ($this->items as $entry) {
            if ($entry->isMain()) {
                return $entry;
            }
        }
    }

    public function suffixed()
    {
        return $this->filter(function (Entry $entry) {
            return $entry->isSuffixed();
        });
    }

    public function suffix($suffix)
    {
        foreach ($this->items as $entry) {
            if ($entry->matchSuffix($suffix)) {
                return $entry;
            }
        }
    }

    public function sorted()
    {
        return $this->sortBy->getSorted();
    }

    public function toPackages()
    {
        return new PackageCollection($this->map->getPackage()->all());
    }

    public function toBase()
    {
        return new Collection($this->items);
    }
}
