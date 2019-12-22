<?php

namespace Anomaly\Streams\Webpack\Command;

use Anomaly\Streams\Platform\Addon\AddonCollection;
use Anomaly\Streams\Webpack\Webpack;

class ResolvePackageAddons
{
    public function handle(Webpack $webpack, AddonCollection $addons)
    {
        $packages = $webpack->getPackages();
        /** @var \Anomaly\Streams\Platform\Addon\Addon $addon */
        foreach ($addons as $addon) {
            $composerName = data_get($addon, 'name');
            if (!$composerName) {
                continue;
            }
            if ($package = $packages->findByComposerName($composerName)) {
                $package->setAddon($addon);
            }
        }
    }
}
