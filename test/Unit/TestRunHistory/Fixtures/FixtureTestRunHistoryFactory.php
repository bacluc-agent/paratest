<?php

declare(strict_types=1);

namespace ParaTest\Tests\Unit\TestRunHistory\Fixtures;

use ParaTest\TestRunHistory\TestRunHistoryFactoryInterface;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;

use function dirname;
use function file_put_contents;

use const DIRECTORY_SEPARATOR;
use const FILE_APPEND;
use const PHP_EOL;

final class FixtureTestRunHistoryFactory implements TestRunHistoryFactoryInterface
{
    public const string CALLS_FILE = 'test-run-history-factory-calls';

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

        file_put_contents(
            dirname($filepath) . DIRECTORY_SEPARATOR . self::CALLS_FILE,
            $filepath . PHP_EOL,
            FILE_APPEND,
        );

        return new FixtureTestRunHistory($filepath);
    }
}
