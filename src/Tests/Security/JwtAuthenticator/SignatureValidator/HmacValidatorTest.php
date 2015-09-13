<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Security\Authenticator\JwtAuthenticator\SignatureValidator;

use KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\SignatureValidator\HmacValidator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class HmacValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private static $secret = 'Mary had a little lamb';

    /**
     * @param string $type
     * @param string $expected
     * @param string $payload
     * @param string $signature
     *
     * @test
     * @dataProvider testSetProvider
     */
    public function willPassTestDataSetUsingSha256($type, $expected, $payload, $signature)
    {
        $validator = new HmacValidator($type);
        $this->assertSame($expected, $validator->isValid($payload, self::$secret, $signature));
    }

    /**
     * Test data created using:
     *  - https://quickhash.com/
     *  - http://www.freeformatter.com/hmac-generator.html
     *
     * @return array
     */
    public static function testSetProvider()
    {
        return [
            [HmacValidator::SHA512, false, ';lkjlkjlkj', 'lkjlkjhlkjh'],
            [
                HmacValidator::SHA512,
                true,
                'fcgtvbhjnkmlijhuiygftdrse53aes64d75f68g7u8hijohugyiftudr',
                'a527da4bd11a4be4c2c40173abca314633dee5fd106dac0ecdfdb7e341b483fb5eb480504b3292f7dfddc32838e99440a3c1ef6b5d3deb8575e49197ee4da45a'
            ],
            [
                HmacValidator::SHA512,
                true,
                'bhyuf76fhgyuftydxcfgxh',
                'ebc146f2241440452459d149d94ad9cb31d0da2a27cbcf1aef10470576b726a0de710cc0d752d2730273039fb329ffd63705fef9df37d3a7a5d6277696a49c29'
            ],
            [
                HmacValidator::SHA512,
                false,
                '1hyuf76fhgyuftydxcfgxh',
                'a527da4bd11a4be4c2c40173abca314633dee5fd106dac0ecdfdb7e341b483fb5eb480504b3292f7dfddc32838e99440a3c1ef6b5d3deb8575e49197ee4da45a'
            ],
            [
                HmacValidator::SHA512,
                false,
                'fcgtvbhjnkmlijhuiygftdrse53aes64d75f68g7u8hijohugyiftud1',
                'ebc146f2241440452459d149d94ad9cb31d0da2a27cbcf1aef10470576b726a0de710cc0d752d2730273039fb329ffd63705fef9df37d3a7a5d6277696a49c29'
            ],
            [HmacValidator::SHA256, false, ';lkjlkjlkj', 'lkjlkjhlkjh'],
            [
                HmacValidator::SHA256,
                true,
                'fcgtvbhjnkmlijhuiygftdrse53aes64d75f68g7u8hijohugyiftudr',
                '8e2ed246e3b0bf02fbd3746f6992e9e5237d72269e3df436019ac3c494db4613'
            ],
            [
                HmacValidator::SHA256,
                true,
                'bhyuf76fhgyuftydxcfgxh',
                'fc086d830f0191838f310f3ed3d254b356bcff5d158ebdb69f20581d48210e45'
            ],
            [
                HmacValidator::SHA256,
                false,
                '1hyuf76fhgyuftydxcfgxh',
                'fc086d830f0191838f310f3ed3d254b356bcff5d158ebdb69f20581d48210e45'
            ],
            [
                HmacValidator::SHA256,
                false,
                'fcgtvbhjnkmlijhuiygftdrse53aes64d75f68g7u8hijohugyiftud1',
                '8a3a90de37b15bf8667e6f69170973087d4673bd99e15ca8a67791187c224d19'
            ],
        ];
    }
}
