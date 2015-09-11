<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Generator;

use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ResourceGenerator extends Generator
{
    /**
     * @param BundleInterface $bundle
     * @param SwaggerDocument $swaggerDoc
     */
    public function generate(BundleInterface $bundle, SwaggerDocument $swaggerDoc)
    {
        $dir = $bundle->getPath();

        $parameters = [
            'namespace' => $bundle->getNamespace(),
            'bundle'    => $bundle->getName(),
        ];

        foreach ($swaggerDoc->getResourceSchemas() as $typeName => $spec) {
            $controllerFile = "$dir/Model/Resources/$typeName.php";
            if (file_exists($controllerFile)) {
                throw new \RuntimeException(sprintf('Resource class "%s" already exists', $typeName));
            }
            $this->renderFile(
                'resource.php.twig',
                $controllerFile,
                array_merge($parameters, $spec)
            );
        }
    }
}
