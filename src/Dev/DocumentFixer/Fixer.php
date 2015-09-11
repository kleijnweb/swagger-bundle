<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Dev\DocumentFixer;

use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;

abstract class Fixer
{
    /**
     * @var Fixer
     */
    private $next;

    /**
     * @param SwaggerDocument $document
     */
    public function fix(SwaggerDocument $document)
    {
        $this->process($document);

        if ($this->next) {
            $this->next->fix($document);
        }
    }

    /**
     * @param Fixer $next
     *
     * @return $this
     */
    public function chain(Fixer $next)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * @param SwaggerDocument $document
     *
     * @return void
     */
    abstract public function process(SwaggerDocument $document);
}
