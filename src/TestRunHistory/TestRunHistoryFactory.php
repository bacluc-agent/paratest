<?php

declare(strict_types=1);

namespace ParaTest\TestRunHistory;

use PHPUnit\Runner\TestRunHistory\DefaultTestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;

use function class_exists;
use function getenv;
use function is_a;
use function is_string;

final class TestRunHistoryFactory
{
    public const string ENV_KEY = 'PARATEST_TEST_RUN_HISTORY_FACTORY';

    public static function create(string $filepath): TestRunHistory
    {
        $factoryClass = self::configuredFactoryClass();

        if ($factoryClass !== null) {
            return (new $factoryClass())->create($filepath);
        }

        return new DefaultTestRunHistory($filepath);
    }

    public static function createForMerge(string $filepath): ?DefaultTestRunHistory
    {
        if (self::configuredFactoryClass() !== null) {
            return null;
        }

        return new DefaultTestRunHistory($filepath);
    }

    /** @return ?class-string<TestRunHistoryFactoryInterface> */
    private static function configuredFactoryClass(): ?string
    {
        $factoryClass = getenv(self::ENV_KEY);

        if (
            is_string($factoryClass)
            && $factoryClass !== ''
            && class_exists($factoryClass)
            && is_a($factoryClass, TestRunHistoryFactoryInterface::class, true)
        ) {
            return $factoryClass;
        }

        return null;
    }
}
