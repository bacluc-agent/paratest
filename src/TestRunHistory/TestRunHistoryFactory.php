<?php

declare(strict_types=1);

namespace ParaTest\TestRunHistory;

use InvalidArgumentException;
use PHPUnit\Runner\TestRunHistory\DefaultTestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;

use function class_exists;
use function getenv;
use function is_a;
use function is_string;
use function sprintf;

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

        if (! is_string($factoryClass) || $factoryClass === '') {
            return null;
        }

        if (! class_exists($factoryClass) || ! is_a($factoryClass, TestRunHistoryFactoryInterface::class, true)) {
            throw new InvalidArgumentException(sprintf(
                '%s is set to "%s", which is not a class implementing %s',
                self::ENV_KEY,
                $factoryClass,
                TestRunHistoryFactoryInterface::class,
            ));
        }

        return $factoryClass;
    }
}
