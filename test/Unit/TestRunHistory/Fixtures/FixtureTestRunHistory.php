<?php

declare(strict_types=1);

namespace ParaTest\Tests\Unit\TestRunHistory\Fixtures;

use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryId;

use function file_put_contents;

final class FixtureTestRunHistory implements TestRunHistory
{
    public const string SENTINEL = 'custom-driver-sentinel';

    public function __construct(private readonly string $filepath)
    {
    }

    public function setStatus(TestRunHistoryId $id, TestStatus $status): void
    {
    }

    public function remove(TestRunHistoryId $id): void
    {
    }

    public function status(TestRunHistoryId $id): TestStatus
    {
        return TestStatus::success();
    }

    public function setTime(TestRunHistoryId $id, float $time): void
    {
    }

    public function time(TestRunHistoryId $id): float
    {
        return 0.0;
    }

    public function load(): void
    {
    }

    public function persist(): void
    {
        file_put_contents($this->filepath, self::SENTINEL);
    }

    public function persistAndPrune(): void
    {
        $this->persist();
    }
}
