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

class SwaggerBundleResponseFixer extends Fixer
{
    /**
     * @param SwaggerDocument $document
     *
     * @return void
     */
    public function process(SwaggerDocument $document)
    {
        $definition = $document->getDefinition();

        if (!isset($definition->responses)) {
            $definition->responses = [];
        }
        if (!isset($definition->responses['ServerError'])) {
            $definition->responses['ServerError'] = [
                'description' => 'Server Error',
                'schema'      => ['$ref' => '#/definitions/VndError']
            ];
        }
        if (!isset($definition->responses['InputError'])) {
            $definition->responses['InputError'] = [
                'description' => 'Input Error',
                'schema'      => ['$ref' => '#/definitions/VndError']
            ];
        }
        if (!isset($definition->definitions)) {
            $definition->definitions = [];
        }
        if (!isset($definition->definitions['VndError'])) {
            $definition->definitions['VndError'] = [
                'type'       => 'object',
                'required'   => ['message', 'logref'],
                'properties' => [
                    'message' => ['type' => 'string'],
                    'logref'  => ['type' => 'string']
                ]
            ];
        }
        foreach ($definition->paths as &$operations) {
            foreach ($operations as &$operation) {
                if (!isset($operation['responses']['500'])) {
                    $operation['responses']['500'] = ['$ref' => '#/responses/ServerError'];
                }
                if (!isset($operation['responses']['400'])) {
                    $operation['responses']['400'] = ['$ref' => '#/responses/InputError'];
                }
            }
        }
    }
}
