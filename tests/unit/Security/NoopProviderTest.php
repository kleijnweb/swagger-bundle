<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Security;

use KleijnWeb\SwaggerBundle\Security\NoopProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class NoopProviderTest extends TestCase
{
    /**
     * @test
     */
    public function authenticateThrowsException()
    {
        $provider = new NoopProvider();
        $this->expectException(\BadMethodCallException::class);
        $provider->authenticate($this->getMockForAbstractClass(TokenInterface::class));
    }

    /**
     * @test
     */
    public function supportsReturnsFalse()
    {
        $provider = new NoopProvider();
        $this->assertFalse($provider->supports($this->getMockForAbstractClass(TokenInterface::class)));
    }
}
