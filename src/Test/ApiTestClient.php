<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Test;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class ApiTestClient extends Client
{
    /**
     * Makes a request from a Request object directly.
     *
     * @param Request $request       A Request instance
     * @param bool    $changeHistory Whether to update the history or not (only used internally for back(), forward(),
     *                               and reload())
     *
     * @return Crawler
     */
    public function requestFromRequest(Request $request, $changeHistory = true)
    {
        return parent::requestFromRequest($request, $changeHistory);
    }
}