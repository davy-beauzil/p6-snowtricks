<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(SetList::DEAD_CODE);
    $rectorConfig->import(SetList::PHP_80);
    $rectorConfig->import(SymfonySetList::SYMFONY_52);
    $rectorConfig->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $rectorConfig->import(SymfonySetList::SYMFONY_52_VALIDATOR_ATTRIBUTES);
    $rectorConfig->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
    $rectorConfig->import(SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES);
    $rectorConfig->import(DoctrineSetList::DOCTRINE_CODE_QUALITY);
    $rectorConfig->import(DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_EXCEPTION);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_SPECIFIC_METHOD);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_91);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_YIELD_DATA_PROVIDER);
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $parameters->set(Option::IMPORT_SHORT_CLASSES, true);

    $services = $rectorConfig->services();
    $services->set(TypedPropertyRector::class);
};
