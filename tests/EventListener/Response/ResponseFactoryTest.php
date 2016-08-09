<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\EventListener\Response;

use KleijnWeb\PhpApi\Descriptions\Description\Operation;
use KleijnWeb\PhpApi\Hydrator\ObjectHydrator;
use KleijnWeb\SwaggerBundle\EventListener\Request\RequestMeta;
use KleijnWeb\SwaggerBundle\EventListener\Response\ResponseFactory;
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
     * @param array      $statusCodes
     * @param array|null $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createResponse(array $statusCodes, array $data = null)
    {
        $operationMock = $this->getMockBuilder(Operation::class)->disableOriginalConstructor()->getMock();
        $operationMock->expects($this->any())
            ->method('getStatusCodes')
            ->willReturn($statusCodes);

        $metaMock = $this->getMockBuilder(RequestMeta::class)->disableOriginalConstructor()->getMock();
        $metaMock->expects($this->any())
            ->method('getOperation')
            ->willReturn($operationMock);

        $hydrator = $this->getMockBuilder(ObjectHydrator::class)->disableOriginalConstructor()->getMock();
        /** @var ObjectHydrator $hydrator */
        $factory = new ResponseFactory($hydrator);
        $request = new Request();
        $request->attributes->set(RequestMeta::ATTRIBUTE_URI, 'tests/Functional/PetStore/app/swagger/composite.yml');
        $request->attributes->set(RequestMeta::ATTRIBUTE, $metaMock);

        return $factory->createResponse($request, $data);
    }
}
