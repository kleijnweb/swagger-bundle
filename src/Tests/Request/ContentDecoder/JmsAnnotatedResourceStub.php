<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request\ContentDecoder;

use JMS\Serializer\Annotation\Type;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JmsAnnotatedResourceStub
{
    /**
     * @var string
     * @Type("string")
     */
    private $foo;

    /**
     * @param string $foo
     *
     * @return $this
     */
    public function setFoo($foo)
    {
        $this->foo = $foo;

        return $this;
    }

    /**
     * @return string
     */
    public function getFoo()
    {
        return $this->foo;
    }
}
