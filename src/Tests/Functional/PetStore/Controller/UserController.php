<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional\PetStore\Controller;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class UserController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param string $username
     * @param string $password
     *
     * @return array
     */
    public function loginUser($username, $password)
    {
        return uniqid();
    }
}
