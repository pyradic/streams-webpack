<?php

namespace Anomaly\Streams\Platform\Webpack;

interface Hydrator
{
    function extract($object): array;

    function hydrate(array $data, $object): void;
}
