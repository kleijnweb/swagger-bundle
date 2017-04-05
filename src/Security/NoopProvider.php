<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Security;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Patchwork to make the security extension work
 *
 * @author John Kleijn <john@kleijnweb.nl>
 */
class NoopProvider implements AuthenticationProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \BadMethodCallException
     */
    public function authenticate(TokenInterface $token)
    {
        throw new \BadMethodCallException("Unsupported");
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return false;
    }
}
