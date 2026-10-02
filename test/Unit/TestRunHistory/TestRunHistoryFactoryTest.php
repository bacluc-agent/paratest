<?php

declare(strict_types=1);

namespace ParaTest\Tests\Unit\TestRunHistory;

use InvalidArgumentException;
use ParaTest\TestRunHistory\TestRunHistoryFactory;
use ParaTest\TestRunHistory\TestRunHistoryFactoryInterface;
use ParaTest\Tests\Unit\TestRunHistory\Fixtures\FixtureTestRunHistory;
use ParaTest\Tests\Unit\TestRunHistory\Fixtures\FixtureTestRunHistoryFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TestRunHistory\DefaultTestRunHistory;

use function putenv;

#[CoversClass(TestRunHistoryFactory::class)]
final class TestRunHistoryFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv(TestRunHistoryFactory::ENV_KEY);
        FixtureTestRunHistoryFactory::reset();

        parent::tearDown();
    }

    public function testCreateReturnsDefaultTestRunHistoryWhenEnvVarNotSet(): void
    {
        putenv(TestRunHistoryFactory::ENV_KEY);

        $history = TestRunHistoryFactory::create('/tmp/paratest-test.cache');

        self::assertInstanceOf(DefaultTestRunHistory::class, $history);
    }

    public function testCreateReturnsCustomHistoryWhenEnvVarSetToValidFactory(): void
    {
        putenv(TestRunHistoryFactory::ENV_KEY . '=' . FixtureTestRunHistoryFactory::class);

        $history = TestRunHistoryFactory::create('/tmp/paratest-test.cache');

        self::assertInstanceOf(FixtureTestRunHistory::class, $history);
    }

    public function testCreateInvokesCustomFactory(): void
    {
        putenv(TestRunHistoryFactory::ENV_KEY . '=' . FixtureTestRunHistoryFactory::class);

        self::assertFalse(FixtureTestRunHistoryFactory::$createCalled);

        TestRunHistoryFactory::create('/tmp/paratest-test.cache');

        self::assertTrue(FixtureTestRunHistoryFactory::$createCalled);
    }

    public function testCreateThrowsWhenEnvVarSetToNonExistentClass(): void
    {
        putenv(TestRunHistoryFactory::ENV_KEY . '=ParaTest\Tests\Unit\TestRunHistory\Fixtures\NonExistentFactory');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains(
            'PARATEST_TEST_RUN_HISTORY_FACTORY is set to '
            . '"ParaTest\Tests\Unit\TestRunHistory\Fixtures\NonExistentFactory", which is not a class implementing '
            . TestRunHistoryFactoryInterface::class,
        );

        TestRunHistoryFactory::create('/tmp/paratest-test.cache');
    }

    public function testCreateThrowsWhenEnvVarSetToClassNotImplementingInterface(): void
    {
        putenv(TestRunHistoryFactory::ENV_KEY . '=' . FixtureTestRunHistory::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains(
            'PARATEST_TEST_RUN_HISTORY_FACTORY is set to "'
            . FixtureTestRunHistory::class . '", which is not a class implementing '
            . TestRunHistoryFactoryInterface::class,
        );

        TestRunHistoryFactory::create('/tmp/paratest-test.cache');
    }

    public function testCreateForMergeReturnsNullWhenCustomFactoryConfigured(): void
    {
        putenv(TestRunHistoryFactory::ENV_KEY . '=' . FixtureTestRunHistoryFactory::class);
        FixtureTestRunHistoryFactory::reset();

        self::assertNull(TestRunHistoryFactory::createForMerge('/tmp/paratest-test.cache'));
        self::assertFalse(FixtureTestRunHistoryFactory::$createCalled);
    }

    public function testCreateForMergeReturnsDefaultHistoryWhenNotConfigured(): void
    {
        putenv(TestRunHistoryFactory::ENV_KEY);

        self::assertInstanceOf(
            DefaultTestRunHistory::class,
            TestRunHistoryFactory::createForMerge('/tmp/paratest-test.cache'),
        );
    }
}
