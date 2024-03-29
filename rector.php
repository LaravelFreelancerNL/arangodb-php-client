<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // define sets of rules
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        LevelSetList::UP_TO_PHP_81,
    ]);

    // Path to PHPStan with extensions, that PHPStan in Rector uses to determine types
    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon.dist');
};
