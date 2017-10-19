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
use KleijnWeb\SwaggerBundle\Serializer\ArraySerializer;
use KleijnWeb\SwaggerBundle\Serializer\SerializerAdapter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ResponseFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willUseFirst2xxStatusCodeFromDocument()
    {
        $this->assertEquals(201, $this->createResponse([], '/pet', 'POST')->getStatusCode());
    }

    /**
     * @test
     */
    public function willUse204ForNullResponsesWhenFoundInDocument()
    {
        $this->assertEquals(204, $this->createResponse(null, '/pet/{id}', 'DELETE')->getStatusCode());
    }

    /**
     * @test
     */
    public function willNotUse204ForNullResponsesWhenNotInDocument()
    {
        $this->assertNotEquals(204, $this->createResponse(null, '/pet/{id}', 'PUT')->getStatusCode());
    }

    /**
     * @test
     */
    public function willReturn204OnEmptyResponseWithMultipl2xxStatusCodesFromDocument()
    {
        $this->assertEquals(204, $this->createResponse(null, '/maintenance', 'GET')->getStatusCode());
    }

    /**
     * @param mixed  $data
     * @param string $path
     * @param string $method
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createResponse($data, $path, $method)
    {
        $serializer = new SerializerAdapter(new ArraySerializer());
        $factory = new ResponseFactory(new DocumentRepository(), $serializer);
        $request = new Request();
        $request->server->set('REQUEST_METHOD', $method);
        $request->attributes->set('_definition', 'src/Tests/Functional/PetStore/app/swagger/composite.yml');
        $request->attributes->set('_swagger_path', $path);

        return $factory->createResponse($request, $data);
    }
}
