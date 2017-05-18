<?php

namespace KleijnWeb\SwaggerBundle\Tests\Serializer;

use KleijnWeb\SwaggerBundle\Serializer\ArraySerializer;

/**
 * @group unit
 */
class ArraySerializerUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ArraySerializer
     */
    private $arraySerializer;

    public function setUp()
    {
        $this->arraySerializer = new ArraySerializer();
    }

    /**
     * Sending a delete request with an empty body should not break the serializer
     * @test
     */
    public function deserializeWillNotBreakOnNullValue()
    {
        $data = $this->arraySerializer->deserialize(null);
        $this->assertNull($data);
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function deserializeWillThrowExceptionOnInvalidData()
    {
        $this->arraySerializer->deserialize('invalid');
    }
}
