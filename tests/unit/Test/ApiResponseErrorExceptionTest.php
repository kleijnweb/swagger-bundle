<?php declare(strict_types=1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Test;

use KleijnWeb\SwaggerBundle\Test\ApiResponseErrorException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ApiResponseErrorExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function exceptionMessageWillIncludeValidationErrors()
    {
        $exception = new ApiResponseErrorException(
            '',
            (object)[
                'message' => 'Validation failed',
                'errors'  => ['a' => 'invalid reason 1', 'b' => 'invalid reason 2'],
            ],
            Response::HTTP_BAD_REQUEST
        );
        $this->assertRegExp('#\[a\]: invalid reason 1\n\[b\]: invalid reason 2#', $exception->getMessage());
    }

    /**
     * @test
     */
    public function getGetDataAndContentFromException()
    {
        $data    = (object)[
            'message' => 'Reason',
            'extra'   => 'foo',
        ];
        $content = 'TheContent';

        $exception = new ApiResponseErrorException($content, $data, Response::HTTP_BAD_REQUEST);

        $this->assertSame($data, $exception->getData());
        $this->assertSame($content, $exception->getContent());
    }
}
