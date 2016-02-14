<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional;

use KleijnWeb\SwaggerBundle\Test\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class GenericDataApiTest extends WebTestCase
{
    use ApiTestCase;

    /**
     * Use config_basic.yml
     *
     * @var bool
     */
    protected $env = 'basic';

    public static function setUpBeforeClass()
    {
        static::initSchemaManager(__DIR__ . '/PetStore/app/swagger/data.yml');
    }

    /**
     * @test
     */
    public function canPost()
    {
        $content = [
            'foo'  => 'bar',
            'blah' => ['foobar']
        ];

        $responseData = $this->post('/data/v1/entity/foo', $content);

        $this->assertInternalType('int', $responseData->id);
        $this->assertSame($content['foo'], $responseData->foo);
        $this->assertSame($content['blah'], $responseData->blah);
        $this->assertSame('foo', $responseData->type);
    }

    /**
     * @test
     */
    public function canFindUsingDateTimeQuery()
    {
        $responseData = $this->get('/data/v1/entity/bar', ['lastModified' => (new \DateTime)->format(\DateTime::W3C)]);

        $this->assertSame(2, $responseData[0]->id);
        $this->assertSame('bar', $responseData[0]->foo);
        $this->assertSame('bar', $responseData[0]->type);
    }

    /**
     * @test
     */
    public function canFindByCriteria()
    {
        $criteria = [
            (object)[
                'fieldName' => 'x',
                'operator'  => 'eq',
                'value'     => 'y'
            ],

            (object)[
                'fieldName' => 'a',
                'operator'  => 'eq',
                'value'     => 'b'
            ]
        ];
        $responseData = $this->post('/data/v1/entity/bar/findByCriteria', $criteria);

        $this->assertSame(3, $responseData[0]->id);
        $this->assertSame('bar', $responseData[0]->foo);
        $this->assertSame('bar', $responseData[0]->type);

        $this->assertSame(4, $responseData[1]->id);
    }

    /**
     * @test
     */
    public function canGet()
    {
        $responseData = $this->get('/data/v1/entity/bar/555');

        $this->assertSame(555, $responseData->id);
        $this->assertSame('bar', $responseData->foo);
        $this->assertSame('bar', $responseData->type);
    }

    /**
     * @test
     */
    public function canDelete()
    {
        $this->assertSame(null, $this->delete('/data/v1/entity/foo/1'));
    }

    /**
     * @test
     */
    public function canPut()
    {
        $content = [
            'foo'  => 'bar',
            'blah' => ['foobar']
        ];

        $responseData = $this->put('/data/v1/entity/foo/999', $content);

        $this->assertSame(999, $responseData->id);
        $this->assertSame($content['foo'], $responseData->foo);
        $this->assertSame($content['blah'], $responseData->blah);
        $this->assertSame('foo', $responseData->type);
    }
}
