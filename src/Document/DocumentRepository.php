<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class DocumentRepository extends ArrayCollection
{
    /**
     * @param string $documentPath
     *
     * @return SwaggerDocument
     */
    public function get($documentPath)
    {
        if (!$documentPath) {
            throw new \InvalidArgumentException("No document path provided");
        }
        $document = parent::get($documentPath);

        if (!$document) {
            $document = new SwaggerDocument($documentPath);
            $this->set($documentPath, $document);
        }

        return $document;
    }
}
