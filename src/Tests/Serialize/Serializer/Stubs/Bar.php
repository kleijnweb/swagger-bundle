<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Serialize\Serializer\Stubs;

class Bar
{
    /**
     * @var float
     */
    private $b;

    /**
     * @var Meh
     */
    private $meh;

    /**
     * @var Meh[]
     */
    private $mehs;

    /**
     * Bar constructor.
     *
     * @param float $b
     * @param Meh   $meh
     * @param Meh[] ...$mehs
     */
    public function __construct(float $b, Meh $meh, Meh ...$mehs)
    {
        $this->b    = $b;
        $this->meh  = $meh;
        $this->mehs = $mehs;
    }
}
