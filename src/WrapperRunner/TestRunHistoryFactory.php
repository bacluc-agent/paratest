<?php

declare(strict_types=1);

namespace ParaTest\WrapperRunner;

use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Runner\TestRunHistory\DefaultTestRunHistory;
use PHPUnit\Runner\TestRunHistory\NullTestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryHandler;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryId;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * PHPUnit has no public API for test run history; all construction of its @internal types lives here
 * so a PHPUnit upgrade that changes them is a one-file change.
 *
 * @internal
 */
final class TestRunHistoryFactory
{
    public static function forConfiguration(Configuration $configuration): TestRunHistory
    {
        if (! $configuration->recordTestRunHistory()) {
            return new NullTestRunHistory();
        }

        $history = new DefaultTestRunHistory($configuration->testRunHistoryFile());
        $history->load();

        return $history;
    }

    public static function handler(string $resultCacheFile): TestRunHistoryHandler
    {
        return new TestRunHistoryHandler(new DefaultTestRunHistory($resultCacheFile), EventFacade::instance(), false);
    }

    public static function forFile(string $file): DefaultTestRunHistory
    {
        return new DefaultTestRunHistory($file);
    }

    public static function durationFor(TestRunHistory $history, Reorderable $test): float
    {
        return $history->time(TestRunHistoryId::fromReorderable($test));
    }
}
