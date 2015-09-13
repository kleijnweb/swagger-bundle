<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Security\Authenticator\JwtAuthenticator\SignatureValidator;

use KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\SignatureValidator\RsaValidator;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RsaValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private static $pubKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwND1VMVJ3BC/aM38tQRH
2GDHecXE8EsGoeAeBR5dFt3QC1/Eoub/F2kee3RBtI6I+kDBjrSDz5lsqh3Sm7N/
47fTKZLvdBaHbCuYXVBQ2tZeEiUBESnsY2HUzXDlqSyDWohuiYeeL6gewxe1CnSE
0l8gYZ0Tx4ViPFYulva6siew0f4tBuSEwSPiKZQnGcssQYJ/VevTD6L4wGoDhkXV
VvJ+qiNgmXXssgCl5vHs22y/RIgeOnDhkj81aB9Evx9iR7DOtyRBxnovrbN5gDwX
m6IDw3fRhZQrVwZ816/eN+1sqpIMZF4oo4kRA4b64U04ex67A/6BwDDQ3LH0mD4d
EwIDAQAB
-----END PUBLIC KEY-----';
    /**
     * @var string
     */
    private static $privKey = '-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAwND1VMVJ3BC/aM38tQRH2GDHecXE8EsGoeAeBR5dFt3QC1/E
oub/F2kee3RBtI6I+kDBjrSDz5lsqh3Sm7N/47fTKZLvdBaHbCuYXVBQ2tZeEiUB
ESnsY2HUzXDlqSyDWohuiYeeL6gewxe1CnSE0l8gYZ0Tx4ViPFYulva6siew0f4t
BuSEwSPiKZQnGcssQYJ/VevTD6L4wGoDhkXVVvJ+qiNgmXXssgCl5vHs22y/RIge
OnDhkj81aB9Evx9iR7DOtyRBxnovrbN5gDwXm6IDw3fRhZQrVwZ816/eN+1sqpIM
ZF4oo4kRA4b64U04ex67A/6BwDDQ3LH0mD4dEwIDAQABAoIBAEej2+NZQi4P0u5/
ymr/YipVGwh1oMyfM6GlgcHpHDFIzOnM9WSJazIpjnfUJC2P3/kLYl9BVtAlcyhp
2DydnuDfBggmXe3GCR75u6zAEKqeh2k6coEMEJaEXOrQDaSjs1JJ6rgSuYV/c56N
CmkODeOUEJX0JMs3Fd7RLpJMreVPD0XjNFIPqKrkkpG4rxteHjtbxfLJlHhVRcSf
IESZ2MdQczLosjsyiv9b/8kp4y4bAR33BaedLxJvxer8qXVKgzw9YHJhWVojFoDx
GwxMR2Eh2hUEAlwifKv84P5GgYdQ0CPHMe06fR+fq/B75dqF/Emlks6f1hoTVEJc
RvXVy7ECgYEA/zUEHdhegUTrFMfvfUrqw+Uolj+GH10yM5EQqYGAiEsNOv+pcR0+
DoNawt1Ddrj4LuvM9s26O26yX99UyleiHphPAOdXUiU7taIWIIcllY+oA9ZajhG8
ufZmCfydeVi+/EjpIrEU3F7q0GXisZ8oMIfg+HG8Z2+uy/+m7k6RCT8CgYEAwWpR
hzW2/4bDpa4P5AfN0wN+CqlGEIfim5MBk608QWgQTPIIADk9dX3IML2BVaX/Eyod
CYn+reF0UDIyaI2XCZ9ab/jeKOQVUh2IksK4T9H41JokWyXoYcVJSF6kIdHOCY4D
GXfx0ruJa2dKGWBMcpSUndXqVGmDvVrcdSOsQy0CgYEA/V19AMRWzq/FU5RLR3Ch
MmrHqKLYXTsBJADZMe/H04fvUquPZSVK/s2Zxew1liB9BNhFXKFSSr7TiCsI2vm1
kfpUNOl3363nYaPRo+mIfrtoaqbcsD8bxuPA2hlZnadltMIN8ssrkr0JEmyUaxM/
qy67Quxnx6kxOIZPDDgj9bECgYBB/JraDVpkrT6cjNkBDCSNhFiBHKU5yJJoT2wv
TOAM1IsTJVPTd7PfHM2qy0yPwvVWPXzwnzLTceKz5ZxLDVtlPgelwftRahdYD5lj
sKkfGlzRc9FJg5JXoO5SkW9f1mt72QZ3rvNC2RaWHQQryi3qvYRCoRO0PtdiR9iE
4mnH9QKBgERffegJiuqdPyAeBZ+5htJ9pdfXjVTgFGuEvhmTR/mrpwqjihLg8IXV
whXkXaMi77FKD9StGrlBOQD6CgUVt/DkiZyGDRX0YkTj78X+hjsrgNdIr8c0TrtQ
gen9FW5mEhj257ZHej/AQrJWFo7YVooBL7ShwU0WaReeQkncxAsY
-----END RSA PRIVATE KEY-----
';

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
        $validator = new RsaValidator($type);
        $this->assertSame($expected, $validator->isValid($payload, self::$pubKey, $signature));
    }

    /**
     * @return array
     */
    public static function testSetProvider()
    {
        // SHA256

        $payload1 = 'some payload';
        $signature1 = '';
        openssl_sign($payload1, $signature1, self::$privKey, RsaValidator::SHA256);

        $payload2 = 'some other payload';
        $signature2 = '';
        openssl_sign($payload2, $signature2, self::$privKey, RsaValidator::SHA256);

        // SHA512

        $payload3 = 'some payload';
        $signature3 = '';
        openssl_sign($payload3, $signature3, self::$privKey, RsaValidator::SHA512);

        $payload4 = 'some other payload';
        $signature4 = '';
        openssl_sign($payload4, $signature4, self::$privKey, RsaValidator::SHA512);

        return [
            [RsaValidator::SHA256, true, $payload1, $signature1],
            [RsaValidator::SHA256, true, $payload2, $signature2],
            [RsaValidator::SHA256, false, $payload1, $signature2],
            [RsaValidator::SHA256, false, $payload2, $signature1],
            [RsaValidator::SHA512, true, $payload3, $signature3],
            [RsaValidator::SHA512, true, $payload4, $signature4],
            [RsaValidator::SHA512, false, $signature4, $signature3],
            [RsaValidator::SHA512, false, $signature3, $signature4],
            [RsaValidator::SHA512, false, $payload1, $signature1],
            [RsaValidator::SHA512, false, $payload2, $signature2],
        ];
    }
}
