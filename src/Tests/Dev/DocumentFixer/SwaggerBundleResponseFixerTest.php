<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Dev\Tests\DocumentFixer;

use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;
use KleijnWeb\SwaggerBundle\Dev\DocumentFixer\Fixers\SwaggerBundleResponseFixer;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class SwaggerBundleResponseFixerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willAddVndErrorSchema()
    {
        $fixer = new SwaggerBundleResponseFixer();
        $document = new SwaggerDocument(__DIR__ . '/assets/minimal.yml');
        $fixer->fix($document);

        $definition = $document->getDefinition();
        $this->assertArrayHasKey('definitions', $definition);
        $this->assertArrayHasKey('VndError', $definition['definitions']);
        $this->assertArrayHasKey('type', $definition['definitions']['VndError']);
        $this->assertArrayHasKey('required', $definition['definitions']['VndError']);
        $this->assertArrayHasKey('properties', $definition['definitions']['VndError']);
    }

    /**
     * @test
     */
    public function willAddServerErrorResponse()
    {
        $fixer = new SwaggerBundleResponseFixer();
        $document = new SwaggerDocument(__DIR__ . '/assets/minimal.yml');
        $fixer->fix($document);

        $definition = $document->getDefinition();
        $this->assertArrayHasKey('responses', $definition);
        $this->assertArrayHasKey('ServerError', $definition['responses']);
        $this->assertArrayHasKey('description', $definition['responses']['ServerError']);
        $this->assertArrayHasKey('schema', $definition['responses']['ServerError']);
        $this->assertArrayHasKey('$ref', $definition['responses']['ServerError']['schema']);
        $this->assertSame($definition['responses']['ServerError']['schema']['$ref'], '#/definitions/VndError');
    }

    /**
     * @test
     */
    public function willAddServerErrorResponseToOperations()
    {
        $fixer = new SwaggerBundleResponseFixer();
        $document = new SwaggerDocument(__DIR__ . '/assets/minimal.yml');
        $fixer->fix($document);

        $operationDefinition = $document->getOperationDefinition('/', 'get');
        $responses = $operationDefinition['responses'];
        $this->assertArrayHasKey('500', $responses);
        $this->assertSame($responses['500']['$ref'], '#/responses/ServerError');
    }

    /**
     * @test
     */
    public function willAddInputErrorResponse()
    {
        $fixer = new SwaggerBundleResponseFixer();
        $document = new SwaggerDocument(__DIR__ . '/assets/minimal.yml');
        $fixer->fix($document);

        $definition = $document->getDefinition();
        $this->assertArrayHasKey('responses', $definition);
        $this->assertArrayHasKey('InputError', $definition['responses']);
        $this->assertArrayHasKey('description', $definition['responses']['InputError']);
        $this->assertArrayHasKey('schema', $definition['responses']['InputError']);
        $this->assertArrayHasKey('$ref', $definition['responses']['InputError']['schema']);
        $this->assertSame($definition['responses']['InputError']['schema']['$ref'], '#/definitions/VndError');
    }

    /**
     * @test
     */
    public function willAddInputErrorResponseToOperations()
    {
        $fixer = new SwaggerBundleResponseFixer();
        $document = new SwaggerDocument(__DIR__ . '/assets/minimal.yml');
        $fixer->fix($document);

        $operationDefinition = $document->getOperationDefinition('/', 'get');
        $responses = $operationDefinition['responses'];
        $this->assertArrayHasKey('400', $responses);
        $this->assertSame($responses['400']['$ref'], '#/responses/InputError');
    }

    /**
     * @test
     */
    public function willAddVndErrorHeaderToOperationResponses()
    {
        $fixer = new SwaggerBundleResponseFixer();
        $document = new SwaggerDocument(__DIR__ . '/assets/minimal.yml');
        $fixer->fix($document);

        $operationDefinition = $document->getOperationDefinition('/', 'get');
        $responses = $operationDefinition['responses'];
        $this->assertArrayHasKey('headers', $responses['500']);
        $this->assertArrayHasKey('headers', $responses['400']);
        $this->assertArrayHasKey('Content-Type', $responses['500']['headers']);
        $this->assertArrayHasKey('Content-Type', $responses['400']['headers']);
        $this->assertSame('application/vnd.error+json', $responses['500']['headers']['Content-Type']);
        $this->assertSame('application/vnd.error+json', $responses['400']['headers']['Content-Type']);
    }
}
