<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Dev\Tests\Response;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Response\ResponseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ResponseFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function willCreateJsonResponseFromData()
    {
        $jsonEncoderMock = $this
            ->getMockBuilder('Symfony\Component\Serializer\Encoder\EncoderInterface')
            ->getMockForAbstractClass();
        $jsonEncoderMock
            ->expects($this->once())
            ->method('encode')
            ->willReturnCallback(function ($string) {
                $data = json_encode($string);
                if (is_null($data)) {
                    throw new \Exception();
                }
            });

        $jsonEncoderMock
            ->expects($this->any())
            ->method('supportsEncoding')
            ->willReturn(true);

        $serializer = new Serializer([], [$jsonEncoderMock]);
        $factory = new ResponseFactory(new DocumentRepository(), $serializer);
        $response = $factory->createResponse(new Request(), [1, 2, 3]);
        $this->assertSame($response->getContent(), '[1,2,3]');
    }
}
