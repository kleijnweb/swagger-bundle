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
use KleijnWeb\SwaggerBundle\Serializer\SerializerAdapter;
use KleijnWeb\SwaggerBundle\Serializer\SymfonySerializerFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ResponseFactorySymfonySerializerCompatibilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @SuppressWarnings(PHPMD.EvalExpression)
     */
    public function willCreateJsonResponseFromObject()
    {
        $className = 'CreateJsonResponseFromObject';
        $number = 0;
        while (class_exists($className)) {
            $className .= ++$number;
        }

        eval("
            class $className {
                public function setFoo(\$foo){ \$this->foo = \$foo; return \$this;}
                public function getFoo(){ return \$this->foo; }
            }
        ");

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

                return $data;
            });

        $jsonEncoderMock
            ->expects($this->any())
            ->method('supportsEncoding')
            ->willReturn(true);

        $serializer = new SerializerAdapter(SymfonySerializerFactory::factory($jsonEncoderMock));
        $factory = new ResponseFactory(new DocumentRepository(), $serializer);
        $request = new Request();
        $request->attributes->set('_definition', 'src/Tests/Functional/PetStore/app/swagger/composite.yml');
        $request->attributes->set('_swagger_path', '/pet/{id}');
        $response = $factory->createResponse($request, (new $className)->setFoo('bar'));

        $expected = json_encode(['foo' => 'bar']);
        $this->assertEquals($expected, $response->getContent());
    }
}
