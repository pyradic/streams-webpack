<?php

namespace Anomaly\Streams\Webpack;

use Laradic\Support\Dot;

class WebpackData extends Dot
{
    public function __construct($items = [])
    {
        parent::__construct($items);

        $this->map('addons', function ($addon) {
            return Dot::wrap($addon)
                ->map('entries', function ($entry) {
                    return Dot::wrap($entry)
                        ->set('scripts', collect($entry['scripts']))
                        ->set('styles', collect($entry['styles']));
                })
                ->copy([
                    'composer.name' => 'composerName',
                    'composer.type' => 'composerType',
                ]);
        });
    }
}
