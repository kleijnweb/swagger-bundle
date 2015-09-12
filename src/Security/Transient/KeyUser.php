<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Security\Transient;

use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class KeyUser implements UserInterface, EquatableInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var Role[]
     */
    private $roles;

    /**
     * @param string $username
     * @param array  $roles
     */
    public function __construct($username, array $roles)
    {
        $this->username = $username;

        foreach ($roles as &$role) {
            if (!$role instanceof Role) {
                $role = new Role($role);
            }
        }

        $this->roles = $roles;
    }

    /**
     * @return Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if ($user instanceof KeyUser
            && $this->username === $user->getUsername()
            && $this->roles == $user->getRoles()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    public function eraseCredentials()
    {
        $this->username = null;
        $this->roles = [];
    }

    /**
     * @return null
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }
}
