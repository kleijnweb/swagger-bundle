<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Security\Key;

use KleijnWeb\SwaggerBundle\Security\UserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class TransientKeyUserProvider extends UserProvider
{
    /**
     * @param string $username
     *
     * @return UserInterface
     */
    public function loadUserByUsername($username)
    {
        return new TransientKeyUser($username, $this->defaultRoles);
    }
}
