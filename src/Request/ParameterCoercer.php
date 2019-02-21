<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Request;

use KleijnWeb\SwaggerBundle\Exception\MalformedContentException;
use KleijnWeb\SwaggerBundle\Exception\UnsupportedException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ParameterCoercer
{
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param object $paramDefinition
     * @param mixed  $value
     *
     * @return mixed
     * @throws MalformedContentException
     * @throws UnsupportedException
     */
    public static function coerceParameter($paramDefinition, $value)
    {
        switch ($paramDefinition->type) {
            case 'string':
                if (!isset($paramDefinition->format)) {
                    return $value;
                }
                switch ($paramDefinition->format) {
                    case 'date':
                        $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', "{$value}T00:00:00Z");
                        if ($dateTime === false) {
                            return $value;
                        }

                        return $dateTime;
                    case 'date-time':
                        $dateTime = \DateTime::createFromFormat(\DateTime::W3C, $value);
                        if ($dateTime === false) {
                            return $value;
                        }

                        return $dateTime;
                    default:
                        return $value;
                }
                break;
            case 'boolean':
                switch ($value) {
                    case 'TRUE':
                    case 'true':
                    case '1':
                        return true;
                    case 'FALSE':
                    case 'false':
                    case '0':
                        return false;
                    default:
                        return $value;
                }
                break;
            case 'number':
                if (!is_numeric($value)) {
                    return $value;
                }

                return (float)$value;
            case 'object':
                if (!is_array($value)) {
                    return $value == '' ? null : $value;
                }
                if (count($value) && is_numeric(key($value))) {
                    return $value;
                }

                return (object)$value;
            case 'array':
                if (is_array($value)) {
                    return $value;
                }
                $format = isset($paramDefinition->collectionFormat)
                    ? $paramDefinition->collectionFormat
                    : 'csv';

                $itemCaster = function ($value) use ($paramDefinition) {
                    if (!isset($paramDefinition->items)) {
                        return $value;
                    }
                    return self::coerceParameter($paramDefinition->items, $value);
                };

                $arrayCaster = function($separator, $value) use($itemCaster){
                    return array_map($itemCaster, explode($separator, $value));
                };

                switch ($format) {
                    case 'csv':
                        return $arrayCaster(',', $value);
                    case 'ssv':
                        return $arrayCaster(' ', $value);
                    case 'tsv':
                        return $arrayCaster("\t", $value);
                    case 'pipes':
                        return $arrayCaster('|', $value);
                    default:
                        throw new UnsupportedException(
                            "Array 'collectionFormat' '$format' is not currently supported"
                        );
                }
                break;
            case 'integer':
                if (!ctype_digit($value)) {
                    return $value;
                }

                return (integer)$value;
            case 'null':
                if ($value !== '') {
                    return $value;
                }

                return null;
            default:
                return $value;
        }
    }
}
