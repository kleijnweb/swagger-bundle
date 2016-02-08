<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class UriBuilder
{

    /**
     * @var string
     */
    private $proto;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $base;

    /**
     * @var SwaggerDocument
     */
    private $document;

    /**
     * Construct the wrapper
     *
     * @param SwaggerDocument $document
     * @param array           $options
     */
    public function __construct(SwaggerDocument $document, array $options = [])
    {
        $this->document = $document;
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'proto':
                    break;
                case 'host':
                    break;
                case 'base':
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown option '$key'");
            }
        }
    }

}
