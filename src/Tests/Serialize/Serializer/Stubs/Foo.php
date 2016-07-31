<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Serialize\Serializer\Stubs;

class Foo
{
    /**
     * @var string
     */
    private $a;

    /**
     * @var Bar
     */
    private $bar;

    /**
     * Foo constructor.
     *
     * @param string   $a
     * @param Bar|null $bar
     */
    public function __construct(string $a, Bar $bar = null)
    {
        $this->a   = $a;
        $this->bar = $bar;
    }
}
