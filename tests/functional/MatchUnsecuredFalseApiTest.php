<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Functional;

use KleijnWeb\SwaggerBundle\Test\ApiTestCase;

/**
 * Tests security with only the default request matcher
 *
 * @author John Kleijn <john@kleijnweb.nl>
 * @group  functional
 */
class MatchUnsecuredFalseApiTest extends BasicSecuredApiTest
{
    /**
     * @var string
     */
    protected $env = 'secure_match_unsecured';
}
