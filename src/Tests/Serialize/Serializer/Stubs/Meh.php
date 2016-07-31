<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Serialize\Serializer\Stubs;

class Meh
{
    /**
     * @var mixed
     */
    private $c;

    /**
     * Meh constructor.
     *
     * @param mixed $c
     */
    public function __construct($c)
    {
        $this->c = $c;
    }
}
