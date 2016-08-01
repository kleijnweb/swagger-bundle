<?php declare(strict_types = 1);
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
        $builder    = new ParameterRefBuilder('/');
        $repository = new DocumentRepository('src/Tests/Functional/PetStore/app');
        $document   = $repository->get('swagger/petstore.yml');
        $request    = Request::create(
            '/pet/100',
            'POST'
        );
        $request->attributes->set('_swagger.file', 'swagger/petstore.yml');
        $request->attributes->set('_swagger.path', '/pet/{petId}');
        $request->attributes->set('_oa_spec', $document);
        $request->attributes->set('_swagger_operation', $document->getOperation('/pet/{petId}', 'POST'));

        $actual = $builder->buildSpecificationLink($request, 'name');

        $this->assertStringStartsWith('http://petstore.swagger.io/swagger/petstore.yml', $actual);
    }

    /**
     * @test
     */
    public function willResortToBestKnownSchemeIfRequestSchemeIsNotInSpec()
    {
        $builder    = new ParameterRefBuilder('/');
        $repository = new DocumentRepository('src/Tests/Functional/PetStore/app');
        $document   = $repository->get('swagger/petstore.yml');
        $request    = Request::create(
            'https://localhost/pet/100',
            'POST'
        );
        $request->attributes->set('_swagger.file', 'swagger/petstore.yml');
        $request->attributes->set('_swagger.path', '/pet/{petId}');
        $request->attributes->set('_oa_spec', $document);
        $request->attributes->set('_swagger_operation', $document->getOperation('/pet/{petId}', 'POST'));

        $actual = $builder->buildSpecificationLink($request, 'name');

        $this->assertStringStartsWith('http://petstore.swagger.io/swagger/petstore.yml', $actual);
    }
}
