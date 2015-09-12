<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
abstract class UserProvider implements UserProviderInterface
{
    /**
     * @var array
     */
    protected $defaultRoles;

    /**
     * @param array $defaultRoles
     */
    public function __construct($defaultRoles = ['IS_AUTHENTICATED_FULLY'])
    {
        $this->defaultRoles = $defaultRoles;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return 'Symfony\Component\Security\Core\User\User' === $class;
    }

    /**
     * Should be implemented when the user's roles might have changed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param UserInterface $user
     *
     * @return void
     */
    public function refreshUser(UserInterface $user)
    {
        // NOOP
    }
}
