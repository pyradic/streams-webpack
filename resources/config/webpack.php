<?php
return [
    'active'     => false,
    'enabled'    => env('WEBPACK_ENABLED', false),
    'path'       => env('WEBPACK_PATH', 'storage/webpack.json'),
    'protocol'   => env('WEBPACK_PROTOCOL', 'http'),
    'host'       => env('WEBPACK_HOST', 'pyro.local'),
    'port'       => env('WEBPACK_PORT', 8079),
    'namespace'  => env('WEBPACK_NAMESPACE', 'pyro'),
    'outputPath' => env('WEBPACK_OUTPUT_PATH', 'public/assets'),
];
