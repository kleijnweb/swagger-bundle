<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Document;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Document\ParameterRefBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ParameterRefBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willDefaultToRequestUri()
    {
        $builder = $this->construct();
        $repository = new DocumentRepository('src/Tests/Functional/PetStore/app');
        $document = $repository->get('swagger/petstore.yml');
        $request = Request::create(
            '/pet/100',
            'POST'
        );
        $request->attributes->set('_definition', 'swagger/petstore.yml');
        $request->attributes->set('_swagger_path', '/pet/{petId}');
        $request->attributes->set('_swagger_document', $document);
        $request->attributes->set('_swagger_operation', $document->getOperationObject('/pet/{petId}', 'POST'));

        $actual = $builder->buildSpecificationLink($request, 'name');

        $this->assertStringStartsWith('http://petstore.swagger.io/swagger/petstore.yml', $actual);
    }

    /**
     * @param string|null $scheme
     * @param string|null $host
     *
     * @return ParameterRefBuilder
     */
    private function construct($scheme = null, $host = null)
    {
        $builder = new ParameterRefBuilder('/', $scheme, $host);

        return $builder;
    }
}
