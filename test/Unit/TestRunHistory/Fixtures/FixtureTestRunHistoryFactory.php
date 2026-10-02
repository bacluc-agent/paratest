<?php

declare(strict_types=1);

namespace ParaTest\Tests\Unit\TestRunHistory\Fixtures;

use ParaTest\TestRunHistory\TestRunHistoryFactoryInterface;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;

final class FixtureTestRunHistoryFactory implements TestRunHistoryFactoryInterface
{
    public static bool $createCalled = false;

    /** @var list<string> */
    public static array $createdFilepaths = [];

    public static function reset(): void
    {
        self::$createCalled     = false;
        self::$createdFilepaths = [];
    }

    public function create(string $filepath): TestRunHistory
    {
        self::$createCalled       = true;
        self::$createdFilepaths[] = $filepath;

        return new FixtureTestRunHistory($filepath);
    }
}
