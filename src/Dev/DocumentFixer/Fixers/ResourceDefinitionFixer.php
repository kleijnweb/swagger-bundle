<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Dev\DocumentFixer\Fixers;

use KleijnWeb\SwaggerBundle\Dev\DocumentFixer\Fixer;
use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;
use KleijnWeb\SwaggerBundle\Serializer\SerializationTypeResolver;

class ResourceDefinitionFixer extends Fixer
{
    /**
     * @var SerializationTypeResolver
     */
    private $typeResolver;

    /**
     * @var string
     */
    private $controllerNameSpace;

    /**
     * @param string                    $controllerNameSpace
     * @param SerializationTypeResolver $typeResolver
     */
    public function __construct($controllerNameSpace, SerializationTypeResolver $typeResolver)
    {
        $this->controllerNameSpace = $controllerNameSpace;
        $this->typeResolver = $typeResolver;
    }

    /**
     * @param SwaggerDocument $document
     *
     * @return void
     */
    public function process(SwaggerDocument $document)
    {
        $definition = $document->getDefinition();

        if (!isset($definition->definitions)) {
            $definition->definitions = [];
        }
        if (!isset($definition->definitions['Pet'])) {
            $definition->definitions['Pet'] = [
                'type'       => 'object',
                'required'   => ['message', 'logref'],
                'properties' => [
                    'message' => ['type' => 'string'],
                    'logref'  => ['type' => 'string']
                ]
            ];
        }
    }
}
