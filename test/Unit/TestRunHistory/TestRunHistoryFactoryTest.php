<?php

declare(strict_types=1);

namespace ParaTest\Tests\Unit\TestRunHistory;

use ParaTest\TestRunHistory\TestRunHistoryFactory;
use ParaTest\Tests\Unit\TestRunHistory\Fixtures\FixtureTestRunHistory;
use ParaTest\Tests\Unit\TestRunHistory\Fixtures\FixtureTestRunHistoryFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TestRunHistory\DefaultTestRunHistory;

use function putenv;

#[CoversClass(TestRunHistoryFactory::class)]
final class TestRunHistoryFactoryTest extends TestCase
{
    private const string ENV_KEY = 'PARATEST_TEST_RUN_HISTORY_FACTORY';

    protected function tearDown(): void
    {
        putenv(self::ENV_KEY);
        FixtureTestRunHistoryFactory::reset();

        parent::tearDown();
    }

    public function testCreateReturnsDefaultTestRunHistoryWhenEnvVarNotSet(): void
    {
        putenv(self::ENV_KEY);

        $history = TestRunHistoryFactory::create('/tmp/paratest-test.cache');

        self::assertInstanceOf(DefaultTestRunHistory::class, $history);
    }

    public function testCreateReturnsCustomHistoryWhenEnvVarSetToValidFactory(): void
    {
        putenv(self::ENV_KEY . '=' . FixtureTestRunHistoryFactory::class);

        $history = TestRunHistoryFactory::create('/tmp/paratest-test.cache');

        self::assertInstanceOf(FixtureTestRunHistory::class, $history);
    }

    public function testCreateInvokesCustomFactory(): void
    {
        putenv(self::ENV_KEY . '=' . FixtureTestRunHistoryFactory::class);

        self::assertFalse(FixtureTestRunHistoryFactory::$createCalled);

        TestRunHistoryFactory::create('/tmp/paratest-test.cache');

        self::assertTrue(FixtureTestRunHistoryFactory::$createCalled);
    }

    public function testCreateFallsBackToDefaultWhenEnvVarSetToNonExistentClass(): void
    {
        putenv(self::ENV_KEY . '=ParaTest\Tests\Unit\TestRunHistory\Fixtures\NonExistentFactory');

        $history = TestRunHistoryFactory::create('/tmp/paratest-test.cache');

        self::assertInstanceOf(DefaultTestRunHistory::class, $history);
    }

    public function testCreateFallsBackToDefaultWhenEnvVarSetToClassNotImplementingInterface(): void
    {
        putenv(self::ENV_KEY . '=' . FixtureTestRunHistory::class);

        $history = TestRunHistoryFactory::create('/tmp/paratest-test.cache');

        self::assertInstanceOf(DefaultTestRunHistory::class, $history);
    }

    public function testCreateForMergeIgnoresCustomFactory(): void
    {
        putenv(self::ENV_KEY . '=' . FixtureTestRunHistoryFactory::class);
        FixtureTestRunHistoryFactory::reset();

        TestRunHistoryFactory::createForMerge('/tmp/paratest-test.cache');

        self::assertFalse(FixtureTestRunHistoryFactory::$createCalled);
    }
}
