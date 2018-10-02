<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Tests\Request\ContentDecoder;

use KleijnWeb\SwaggerBundle\Request\ParameterCoercer;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ParameterCoercerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider conversionProvider
     * @test
     *
     * @param string $type
     * @param mixed  $value
     * @param mixed  $expected
     * @param string $format
     */
    public function willInterpretValuesAsExpected($type, $value, $expected, $format = null)
    {
        $spec = ['type' => $type, 'name' => $value];
        if ($type === 'array') {
            $spec['collectionFormat'] = $format;
        }
        if ($type === 'string') {
            $spec['format'] = $format;
        }

        $actual = ParameterCoercer::coerceParameter((object)$spec, $value);

        if (is_object($expected)) {
            $this->assertEquals($expected, $actual);
            return;
        }
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function willCastArrayItems()
    {
        $spec = [
            'type' => 'array',
            'collectionFormat' => 'csv',
            'items' => (object)[
                'type' => 'integer'
            ]
        ];

        $actual = ParameterCoercer::coerceParameter((object)$spec, '1,2,3');
        $expected = [1, 2, 3];
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider malformedConversionProvider
     * @test
     *
     * @param string $type
     * @param mixed  $value
     */
    public function willNotChangeUninterpretableValues($type, $value)
    {
        $actual = ParameterCoercer::coerceParameter((object)['type' => $type, 'name' => $value], $value);
        $this->assertSame($value, $actual);
    }

    /**
     * @dataProvider malformedDateTimeConversionProvider
     * @test
     *
     * @param string $format
     * @param mixed  $value
     */
    public function willNotChangeUninterpretableDateTimeAsExpected($format, $value)
    {
        $actual = ParameterCoercer::coerceParameter(
            (object)['type' => 'string', 'format' => $format, 'name' => $value],
            $value
        );
        $this->assertSame($value, $actual);
    }

    /**
     * @dataProvider unsupportedPrimitiveConversionProvider
     * @test
     *
     * @expectedException \KleijnWeb\SwaggerBundle\Exception\UnsupportedException
     *
     * @param array $spec
     * @param mixed $value
     */
    public function willThrowUnsupportedExceptionInPredefinedCases($spec, $value)
    {
        $spec = array_merge(['type' => 'string', 'name' => $value], $spec);
        ParameterCoercer::coerceParameter((object)$spec, $value);
    }

    /**
     * @return array
     */
    public static function conversionProvider()
    {
        $now       = new \DateTime('2016-01-01 12:00:00.00000');
        $midnight  = new \DateTime('midnight today');
        $object    = new \stdClass;
        $object->a = 'b';
        $object->c = 'd';

        return [
            ['boolean', '0', false],
            ['boolean', 'FALSE', false],
            ['boolean', 'false', false],
            ['boolean', '1', true],
            ['boolean', 'TRUE', true],
            ['boolean', 'true', true],
            ['integer', '1', 1],
            ['integer', '21474836470', 21474836470],
            ['integer', '00005', 5],
            ['number', '1', 1.0],
            ['number', '1.5', 1.5],
            ['number', '1', 1.0],
            ['number', '1.5', 1.5],
            ['string', '1', '1'],
            ['string', '1.5', '1.5'],
            ['string', '€', '€'],
            ['null', '', null],
            ['string', $midnight->format('Y-m-d'), $midnight, 'date'],
            ['string', $now->format(\DateTime::W3C), $now, 'date-time'],
            ['array', [1, 2, 3, 4], [1, 2, 3, 4]],
            ['array', 'a', ['a']],
            ['array', 'a,b,c', ['a', 'b', 'c']],
            ['array', 'a, b,c ', ['a', ' b', 'c ']],
            ['array', 'a', ['a'], 'ssv'],
            ['array', 'a b c', ['a', 'b', 'c'], 'ssv'],
            ['array', 'a  b c ', ['a', '', 'b', 'c', ''], 'ssv'],
            ['array', 'a', ['a'], 'tsv'],
            ['array', "a\tb\tc", ['a', 'b', 'c'], 'tsv'],
            ['array', "a\t b\tc ", ['a', ' b', 'c '], 'tsv'],
            ['array', 'a', ['a'], 'pipes'],
            ['array', 'a|b|c', ['a', 'b', 'c'], 'pipes'],
            ['array', 'a| b|c ', ['a', ' b', 'c '], 'pipes'],
            ['object', ['a' => 'b', 'c' => 'd'], $object],
            ['object', '', null]
        ];
    }

    /**
     * @return array
     */
    public static function malformedConversionProvider()
    {
        return [
            ['boolean', 'a'],
            ['boolean', ''],
            ['boolean', "\0"],
            ['boolean', null],
            ['integer', '1.0'],
            ['integer', 'TRUE'],
            ['integer', ''],
            ['number', 'b'],
            ['number', 'FALSE'],
            ['null', '0'],
            ['null', 'FALSE'],
            ['object', ['a', 'c']],
            ['object', 'FALSE']
        ];
    }

    /**
     * @return array
     */
    public static function malformedDateTimeConversionProvider()
    {
        return [
            ['date', '01-01-1970'],
            ['date-time', '1970-01-01TH:i:s'], # Missing timezone
        ];
    }

    /**
     * @return array
     */
    public static function unsupportedPrimitiveConversionProvider()
    {
        return [
            [['type' => 'array', 'collectionFormat' => 'multi'], ''],
            [['type' => 'array', 'collectionFormat' => 'foo'], ''],
        ];
    }
}
