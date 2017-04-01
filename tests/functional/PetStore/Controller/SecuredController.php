<?php declare(strict_types = 1);
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
class SecuredController
{
    /**
     * @return string
     */
    public function secure(): string
    {
        return 'SECURED CONTENT';
    }

    /**
     * @return string
     */
    public function unsecured(): string
    {
        return 'UNSECURED CONTENT';
    }
}
