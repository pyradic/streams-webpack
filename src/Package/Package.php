<?php

namespace Anomaly\Streams\Webpack\Package;

use Anomaly\Streams\Platform\Addon\Addon;
use Laradic\Support\Traits\ArrayAccessibleProperties;

class Package implements \ArrayAccess
{
    use ArrayAccessibleProperties;

    //@formatter:off
    /** @var \Anomaly\Streams\Webpack\Webpack */
    protected $webpack;
    /** @var \Anomaly\Streams\Webpack\Package\EntryCollection|\Anomaly\Streams\Webpack\Package\Entry[] */
    protected $entries;
    /** @var \Anomaly\Streams\Platform\Addon\Addon */
    protected $addon;

    /** @var string */
    protected $name;
    /** @var string */
    protected $firstName;
    /** @var string */
    protected $firstNameSnake;
    /** @var string */
    protected $lastName;
    /** @var string */
    protected $lastNameSnake;
    /** @var string */
    protected $exportName;
    /** @var string */
    protected $path;
    /** @var string */
    protected $srcPath;
    /** @var string */
    protected $sorted;

    /** @var string */
    protected $composerName;
    /** @var string */
    protected $composerType;
    //@formatter:on

    /**
     * Package constructor.
     *
     * @param \Anomaly\Streams\Webpack\Webpack           $webpack
     * @param array|\Laradic\Support\Dot $data
     */
    public function __construct(\Anomaly\Streams\Webpack\Webpack $webpack)
    {
        $this->webpack = $webpack;
    }

    public function getEntries()
    {
        return $this->entries;
    }

    public function setEntries($entries)
    {
        $this->entries = $entries;
        return $this;
    }

    public function getMainEntry()
    { }

    public function hasAddon()
    {
        return $this->addon instanceof Addon;
    }

    public function getAddon()
    {
        return $this->addon;
    }

    public function setAddon($addon)
    {
        $this->addon = $addon;
        return $this;
    }

    public function isComposerStreamAddon()
    {
        return $this->composerType === 'stream-addon';
    }

    // generated

    public function getWebpack()
    {
        return $this->webpack;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getExportName()
    {
        return $this->exportName;
    }

    public function getPath()
    {
        return base_path($this->path);
    }

    public function getSorted()
    {
        return $this->sorted;
    }

    public function getComposerName()
    {
        return $this->composerName;
    }

    public function getComposerType()
    {
        return $this->composerType;
    }
}
//        public function getFirstName()
//        {
//            return $this->firstName;
//        }
//
//        public function getFirstNameSnake()
//        {
//            return $this->firstNameSnake;
//        }
//
//        public function getLastName()
//        {
//            return $this->lastName;
//        }
//
//        public function getLastNameSnake()
//        {
//            return $this->lastNameSnake;
//        }
