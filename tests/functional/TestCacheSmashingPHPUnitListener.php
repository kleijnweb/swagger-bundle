<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;

class TestCacheSmashingPHPUnitListener implements TestListener
{
    const SUITE_NAME = 'Functional';

    public function addError(Test $test, \Throwable $t, float $time): void
    {
        //NOOP
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        //NOOP
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        //NOOP
    }

    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
        //NOOP
    }

    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
        //NOOP
    }

    public function addSkippedTest(Test $test, \Throwable $t, float$time): void
    {
        //NOOP
    }

    public function startTest(Test $test): void
    {
        //NOOP
    }

    public function endTest(Test $test, float $time): void
    {
        //NOOP
    }

    public function startTestSuite(TestSuite $suite): void
    {
        $this->smashIfFunctionalSuite($suite);
    }

    public function endTestSuite(TestSuite $suite): void
    {
        $this->smashIfFunctionalSuite($suite);
    }

    private function smashIfFunctionalSuite(TestSuite $suite)
    {
        if ($suite->getName() !== self::SUITE_NAME) {
            return;
        }

        $dir = __DIR__ . '/var';

        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $path) {
            $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }
    }
}
