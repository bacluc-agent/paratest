<?php

declare(strict_types=1);

namespace ParaTest\TestRunHistory;

use PHPUnit\Runner\TestRunHistory\TestRunHistory;

interface TestRunHistoryFactoryInterface
{
    public function create(string $filepath): TestRunHistory;
}
