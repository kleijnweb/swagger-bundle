<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Response;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Response\ResponseFactory;
use KleijnWeb\SwaggerBundle\Serializer\JmsSerializerFactory;
use KleijnWeb\SwaggerBundle\Serializer\SerializerAdapter;
use KleijnWeb\SwaggerBundle\Tests\Request\ContentDecoder\JmsAnnotatedResourceStub;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ResponseFactoryJmsSerializerCompatibilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willCreateJsonResponseFromObject()
    {
        $serializer = new SerializerAdapter(JmsSerializerFactory::factory());
        $factory = new ResponseFactory(new DocumentRepository(), $serializer);
        $request = new Request();
        $request->attributes->set('_definition', 'src/Tests/Functional/PetStore/app/swagger/composite.yml');
        $request->attributes->set('_swagger_path', '/pet/{id}');
        $response = $factory->createResponse($request, (new JmsAnnotatedResourceStub())->setFoo('bar'));

        $expected = json_encode(
            ['foo' => 'bar']
        );
        $this->assertEquals($expected, $response->getContent());
    }
}
