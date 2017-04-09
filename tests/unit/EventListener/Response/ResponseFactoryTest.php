<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener\Response;

use KleijnWeb\PhpApi\Descriptions\Description\Operation;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Schema;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\SchemaValidator;
use KleijnWeb\PhpApi\Descriptions\Description\Schema\Validator\ValidationResult;
use KleijnWeb\PhpApi\Hydrator\ObjectHydrator;
use KleijnWeb\PhpApi\RoutingBundle\Routing\RequestMeta;
use KleijnWeb\SwaggerBundle\EventListener\Response\ResponseFactory;
use KleijnWeb\SwaggerBundle\Exception\ValidationException;
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
        $this->assertEquals(201, $this->createResponse([201, 200], [])->getStatusCode());
    }

    /**
     * @test
     */
    public function willUse204ForNullResponsesWhenFoundInDocument()
    {
        $this->assertEquals(204, $this->createResponse([200, 201, 204])->getStatusCode());
    }

    /**
     * @test
     */
    public function willNotUse204ForNullResponsesWhenNotInDocument()
    {
        $this->assertNotEquals(204, $this->createResponse([200, 201])->getStatusCode());
    }

    /**
     * @test
     */
    public function canValidateUsingSchemaAndBody()
    {
        $this->assertNotEquals(204, $this->createResponse([200, 201], (object)[], true)->getStatusCode());
    }

    /**
     * @test
     */
    public function canInvalidateUsingSchemaAndBody()
    {
        try {
            $this->assertNotEquals(204, $this->createResponse([200, 201], (object)[], true, false)->getStatusCode());
        } catch (ValidationException $e) {
            $this->assertSame(['foo' => 'invalid'], $e->getValidationErrors());
        }
    }

    /**
     * @param array $statusCodes
     * @param mixed $data
     *
     * @param bool  $useValidator
     *
     * @param bool  $stubValid
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \KleijnWeb\SwaggerBundle\Exception\ValidationException
     */
    private function createResponse(array $statusCodes, $data = null, $useValidator = false, $stubValid = true)
    {
        $operationMock = $this->getMockBuilder(Operation::class)->disableOriginalConstructor()->getMock();
        $operationMock->expects($this->any())
            ->method('getStatusCodes')
            ->willReturn($statusCodes);

        $metaMock = $this->getMockBuilder(RequestMeta::class)->disableOriginalConstructor()->getMock();
        $metaMock->expects($this->any())
            ->method('getOperation')
            ->willReturn($operationMock);

        $mockValidator = null;
        if ($useValidator) {
            $mockValidator = $this->getMockBuilder(SchemaValidator::class)->disableOriginalConstructor()->getMock();
            $mockValidator
                ->expects($this->once())
                ->method('validate')
                ->with($this->isInstanceOf(Schema::class), $data)
                ->willReturn(
                    $stubValid ? new ValidationResult(true) : new ValidationResult(false, ['foo' => 'invalid'])
                );
        }

        $hydrator = $this->getMockBuilder(ObjectHydrator::class)->disableOriginalConstructor()->getMock();
        $hydrator
            ->expects($this->any())
            ->method('dehydrate')
            ->willReturn($data);

        $factory = new ResponseFactory($hydrator, $mockValidator);
        $request = new Request();
        $request->attributes->set(RequestMeta::ATTRIBUTE_URI, 'tests/Functional/PetStore/app/swagger/composite.yml');
        $request->attributes->set(RequestMeta::ATTRIBUTE, $metaMock);

        return $factory->createResponse($request, $data);
    }
}
