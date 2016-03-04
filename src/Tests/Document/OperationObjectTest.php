<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Document;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Document\OperationObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class OperationObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canBuildBodyReference()
    {
        $repository = new DocumentRepository('src/Tests/Functional/PetStore/app');
        $document   = $repository->get('swagger/petstore.yml');
        $operation  = new OperationObject($document, '/store/order', 'post');

        $pointer = $operation->createParameterSchemaPointer('body.properties.quantity');

        $expected = '/paths/~1store~1order/post/x-request-schema/properties/body/properties/quantity';
        $this->assertEquals($expected, $pointer);
    }
}
