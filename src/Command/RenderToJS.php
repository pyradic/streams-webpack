<?php

namespace Anomaly\Streams\Platform\Webpack\Command;

use Collective\Html\HtmlBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Laradic\Support\Dot;
use Anomaly\Streams\Platform\Webpack\Webpack;

class RenderToJS
{
    /** @var array = static::_example_options() */
    protected $options = [];

    protected static $defaults = [
        'root'          => 'window',
        'namespace'     => null,
        'key'           => null,
        'json_options'  => null,
        'assign_by_key' => false,
        'no_wrap'       => false,
    ];

    protected $value;

    /**
     * @var \Pyro\Webpack\Webpack
     */
    protected $webpack;

    /**
     * @var \Collective\Html\HtmlBuilder
     */
    protected $html;

    /**
     * RenderToJS constructor.
     *
     * @param mixed       $value
     * @param string|null $key
     * @param array       $options = static::_example_options()
     */
    public function __construct($value, string $key = null, array $options = [])
    {
        $this->value = $value;
        if ($key) {
            $options['key'] = $key;
        }
        $this->options = array_replace(static::$defaults, $options);
    }

    protected function getNamespace()
    {
        return $this->options['namespace'] ?? $this->webpack->getNamespace();
    }

    public function handle(HtmlBuilder $html, Webpack $webpack)
    {
        $this->html    = $html;
        $this->webpack = $webpack;
        $lines         = [];
        $value         = $this->getValue();
        $namespace     = $this->getNamespace();
        $target        = "{$this->options['root']}['{$namespace}']";
        if ($this->options['key']) {
            $lines[] = "{$target} = {$target} || {};";
            $target  .= "['{$this->options['key']}']";
        }
        if ($this->options['assign_by_key']) {
            $lines[] = "{$target} = {$target} || {};";
        }

        if ($this->options['assign_by_key']) {
            foreach ($value->keys() as $key) {
                $json    = $value->toJson($key, $this->options['json_options']);
                $lines[] = "{$target}['{$key}'] = {$json};";
            }
        } else {
            $json    = $value->toJson($this->options['json_options']);
            $lines[] = "{$target} = {$json};";
        }

        $js = implode("\n", $lines);
        if ($this->options['no_wrap']) {
            return $js;
        }
        return $html->tag('script', $js);
    }

    protected function getValue(): Dot
    {
        $value = $this->value;
        if (!$value instanceof Dot) {
            $value = Collection::wrap($value)->toDot();
        }
        return $value;
    }

    public static function _example_options()
    {
        return [
            'root'          => 'window',
            'key'           => '',
            'json_options'  => '',
            'assign_by_key' => false,
            'no_wrap'       => false,
        ];
    }
}

//class StringBuilder
//{
//    protected $lines = [];
//
//    protected $line = 0;
//
//    protected $delimiter = PHP_EOL;
//
//    public static function make(string $initialString = '', $explodeInitialString = false)
//    {
//        return new static($initialString, $explodeInitialString);
//    }
//
//    public function __construct(string $initialString = '', $explodeInitialString = false)
//    {
//        if ($explodeInitialString) {
//            $initialStrings = explode($this->delimiter, $initialString);
//        } else {
//            $initialStrings = Arr::wrap($initialString);
//        }
//        $this->lines = $initialStrings;
//        $this->line  = count($this->lines) - 1;
//    }
//
//    public function lines($lines)
//    {
//        foreach ($lines as $line) {
//            $this->line($line);
//        }
//        return $this;
//    }
//
//    public function line($string, $line = null)
//    {
//        $line = $line ?? $this->line;
//        if ($line === 0) {
//            $this->lines[ 0 ] = $string;
//        }
//        $this->lines[ $line ] = $string;
//        $this->line++;
//        return $this;
//    }
//
//    public function append($string)
//    {
//        $this->lines[ $this->line - 1 ] .= $string;
//        return $this;
//    }
//
//    public function prepend($string)
//    {
//        $this->lines[ $this->line - 1 ] .= $string;
//        return $this;
//    }
//
//    public function replace(string $search, string $replace, $line = null)
//    {
//
//    }
//
//    public function replaceAll(string $search, string $replace)
//    {
//
//    }
//
//    public function __toString()
//    {
//        return $this->build();
//    }
//
//    public function build($glue = PHP_EOL)
//    {
//        return implode($glue, $this->lines);
//    }
//}
