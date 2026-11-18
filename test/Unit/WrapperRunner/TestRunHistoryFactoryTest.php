<?php

declare(strict_types=1);

namespace ParaTest\Tests\Unit\WrapperRunner;

use ParaTest\Tests\fixtures\multi_method_tests\AlphaTest;
use ParaTest\Tests\TestBase;
use ParaTest\WrapperRunner\TestRunHistoryFactory;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TestRunHistory\DefaultTestRunHistory;
use PHPUnit\Runner\TestRunHistory\NullTestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryId;
use ReflectionProperty;

use function explode;
use function file_put_contents;
use function is_subclass_of;

use const DIRECTORY_SEPARATOR;

/** @internal */
#[CoversClass(TestRunHistoryFactory::class)]
final class TestRunHistoryFactoryTest extends TestBase
{
    public function testForConfigurationReturnsNullHistoryWhenNotRecording(): void
    {
        $configurationFile = $this->tmpDir . DIRECTORY_SEPARATOR . 'phpunit.xml';
        file_put_contents($configurationFile, <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit recordTestRunHistory="false">
                <testsuites>
                    <testsuite name="Multi method">
                        <directory>{$this->fixture('multi_method_tests')}</directory>
                    </testsuite>
                </testsuites>
            </phpunit>
            XML);

        $options = $this->createOptionsFromArgv([
            '--configuration'   => $configurationFile,
            '--cache-directory' => $this->tmpDir,
        ]);

        self::assertInstanceOf(NullTestRunHistory::class, TestRunHistoryFactory::forConfiguration($options->configuration));
    }

    public function testForConfigurationReturnsLoadedHistoryWithRecordedDuration(): void
    {
        $this->writeTestRunHistory([AlphaTest::class . '::testOne' => 1.25]);

        $options = $this->createOptionsFromArgv([
            '--configuration'   => $this->fixture('phpunit-multi_method_tests.xml'),
            '--cache-directory' => $this->tmpDir,
        ]);

        $history = TestRunHistoryFactory::forConfiguration($options->configuration);

        self::assertInstanceOf(DefaultTestRunHistory::class, $history);
        self::assertSame(1.25, $history->time(TestRunHistoryId::fromTestClassAndMethodName(AlphaTest::class, 'testOne')));
    }

    public function testHandlerReturnsTestRunHistoryHandler(): void
    {
        $instance = new ReflectionProperty(Facade::class, 'instance');
        $keep     = $instance->getValue();
        $instance->setValue(null, null);

        $file = $this->tmpDir . DIRECTORY_SEPARATOR . 'result-cache';

        try {
            $handler = TestRunHistoryFactory::handler($file);
            $handler->testRunnerExecutionFinished();
            self::assertFileExists($file);
        } finally {
            $instance->setValue(null, $keep);
        }
    }

    public function testForFileReturnsHistoryBoundToPath(): void
    {
        $file    = $this->tmpDir . DIRECTORY_SEPARATOR . 'test-run-history';
        $history = TestRunHistoryFactory::forFile($file);

        $history->setTime(TestRunHistoryId::fromTestClassAndMethodName(AlphaTest::class, 'testOne'), 2.5);
        $history->persist();

        $reloaded = TestRunHistoryFactory::forFile($file);
        $reloaded->load();
        self::assertSame(2.5, $reloaded->time(TestRunHistoryId::fromTestClassAndMethodName(AlphaTest::class, 'testOne')));
    }

    public function testDurationForReturnsRecordedDuration(): void
    {
        $this->writeTestRunHistory([AlphaTest::class . '::testOne' => 3.75]);

        $history = TestRunHistoryFactory::forFile($this->tmpDir . DIRECTORY_SEPARATOR . 'test-run-history');
        $history->load();

        $test = new AlphaTest('testOne');

        self::assertSame(3.75, TestRunHistoryFactory::durationFor($history, $test));
    }

    /** @param array<non-empty-string, float> $durations */
    private function writeTestRunHistory(array $durations): void
    {
        $history = TestRunHistoryFactory::forFile($this->tmpDir . DIRECTORY_SEPARATOR . 'test-run-history');
        foreach ($durations as $test => $duration) {
            [$class, $method] = explode('::', $test);
            self::assertTrue(is_subclass_of($class, TestCase::class));
            $history->setTime(TestRunHistoryId::fromTestClassAndMethodName($class, $method), $duration);
        }

        $history->persist();
    }
}
