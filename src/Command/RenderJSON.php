<?php

namespace Anomaly\Streams\Webpack\Command;

use Collective\Html\HtmlBuilder;
use Illuminate\Support\Collection;
use Laradic\Support\Dot;
use Anomaly\Streams\Webpack\Webpack;

class RenderJSON
{
    protected $options = [
        'root'      => 'window',
        'type'      => 'global', // global,namespace
        'namespace' => null,

        'json_options'  => null,
        'assign_by_key' => false,
        'no_wrap'       => false,
    ];

    protected $key;

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
     * RenderJSON constructor.
     *
     * @param       $key
     * @param       $value
     * @param array $options = static::defaults()
     */
    public function __construct($value, $key = null, array $options = [])
    {
        $this->options = $options;
        $this->key     = $key;
        $this->value   = $value;
    }

    protected static function defaults()
    {
        return [
            'root'      => 'window',
            'type'      => 'global', // global,namespace
            'namespace' => null,

            'json_options'  => null,
            'assign_by_key' => false,
            'no_wrap'       => false,
        ];
    }

    public static function global($value, $key = null)
    {
        return new static($value, $key, ['type' => 'global']);
    }

    public static function namespace($value, $key = null, $namespace = null)
    {
        return new static($value, $key, ['type' => 'namespace', 'namespace' => $namespace]);
    }

    /**
     * @param array $options = static::defaults()
     *
     * @return $this
     */
    public function configure($options)
    {
        $this->options = Collection::wrap($this->options)->merge(Collection::wrap($options))->toArray();
        return $this;
    }

    public function handle(HtmlBuilder $html, Webpack $webpack)
    {
        $this->html    = $html;
        $this->webpack = $webpack;
        $lines         = [];
        $value         = $this->getValue();
        $options       = array_replace(static::defaults(), $this->options);
        $target        = $options['root'];

        if ($options['type'] === 'namespace') {
            $namespace = $options['namespace'] ?? $webpack->getNamespace();
            $target    .= "['{$namespace}']";
            $lines[]   = "{$target} = {$target} || {};";
        } elseif ($options['type'] === 'global') { }

        if ($this->key) {
            $target .= "['{$this->key}']";
        }

        if ($options['assign_by_key']) {
            $lines[] = "{$target} = {$target} || {};";
            foreach ($value->keys() as $key) {
                $json    = $value->toJson($key, $options['json_options']);
                $lines[] = "{$target}['{$key}'] = {$json};";
            }
        } else {
            $json    = $value->toJson($options['json_options']);
            $lines[] = "{$target} = {$json};";
        }

        $js = implode("\n", $lines);
        if ($options['no_wrap']) {
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

    public function assignByKey($value = true)
    {
        $this->options['assign_by_key'] = $value;
        return $this;
    }

    public function noWrap($value = true)
    {
        $this->options['no_wrap'] = $value;
        return $this;
    }

    public function jsonOptions($value = null)
    {
        $this->options['json_options'] = $value;
        return $this;
    }
}
