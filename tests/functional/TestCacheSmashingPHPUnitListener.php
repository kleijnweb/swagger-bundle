<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional;

use PHPUnit_Framework_Test;
use PHPUnit_Framework_AssertionFailedError;
use Exception;
use PHPUnit_Framework_TestSuite;

class TestCacheSmashingPHPUnitListener implements \PHPUnit_Framework_TestListener
{
    const SUITE_NAME = 'Functional';

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        //NOOP
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        //NOOP
    }

    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        //NOOP
    }

    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        //NOOP
    }

    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        //NOOP
    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
        //NOOP
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        //NOOP
    }

    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->smashIfFunctionalSuite($suite);
    }

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->smashIfFunctionalSuite($suite);
    }

    private function smashIfFunctionalSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ($suite->getName() !== self::SUITE_NAME) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(__DIR__ . '/PetStore/app/cache', \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $path) {
            $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }
    }
}