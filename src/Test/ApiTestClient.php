<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Test;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Link;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Profiler\Profile as HttpProfile;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 * @codeCoverageIgnore
 */
class ApiTestClient extends Client
{
    /**
     * @var Client
     */
    private $subject;

    /**
     * ApiTestClient constructor.
     *
     * @param Client $subject
     */
    public function __construct(Client $subject)
    {
        $this->subject = $subject;
    }

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
        return $this->subject->requestFromRequest($request, $changeHistory);
    }

    /**
     * Returns the container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->subject->getContainer();
    }

    /**
     * Returns the kernel.
     *
     * @return KernelInterface
     */
    public function getKernel()
    {
        return $this->subject->getKernel();
    }

    /**
     * Gets the profile associated with the current Response.
     *
     * @return HttpProfile A Profile instance
     */
    public function getProfile()
    {
        return $this->subject->getProfile();
    }

    /**
     * Enables the profiler for the very next request.
     *
     * If the profiler is not enabled, the call to this method does nothing.
     */
    public function enableProfiler()
    {
        $this->subject->enableProfiler();
    }

    /**
     * {@inheritdoc}
     *
     * @param Request $request A Request instance
     *
     * @return Response A Response instance
     */
    protected function doRequest($request)
    {
        return $this->subject->doRequest($request);
    }

    /**
     * {@inheritdoc}
     *
     * @param Request $request A Request instance
     *
     * @return Response A Response instance
     */
    protected function doRequestInProcess($request)
    {
        return $this->subject->doRequestInProcess($request);
    }

    /**
     * Returns the script to execute when the request must be insulated.
     *
     * It assumes that the autoloader is named 'autoload.php' and that it is
     * stored in the same directory as the kernel (this is the case for the
     * Symfony Standard Edition). If this is not your case, create your own
     * client and override this method.
     *
     * @param Request $request A Request instance
     *
     * @return string The script content
     */
    protected function getScript($request)
    {
        return $this->subject->getScript($request);
    }

    /**
     * Sets whether to automatically follow redirects or not.
     *
     * @param bool $followRedirect Whether to follow redirects
     *
     * @api
     */
    public function followRedirects($followRedirect = true)
    {
        $this->subject->followRedirects($followRedirect);
    }

    /**
     * Sets the maximum number of requests that crawler can follow.
     *
     * @param int $maxRedirects
     */
    public function setMaxRedirects($maxRedirects)
    {
        $this->subject->setMaxRedirects($maxRedirects);
    }

    /**
     * Sets the insulated flag.
     *
     * @param bool $insulated Whether to insulate the requests or not
     *
     * @throws \RuntimeException When Symfony Process Component is not installed
     *
     * @api
     */
    public function insulate($insulated = true)
    {
        $this->subject->insulate($insulated);
    }

    /**
     * Sets server parameters.
     *
     * @param array $server An array of server parameters
     *
     * @api
     */
    public function setServerParameters(array $server)
    {
        $this->subject->setServerParameters($server);
    }

    /**
     * Sets single server parameter.
     *
     * @param string $key   A key of the parameter
     * @param string $value A value of the parameter
     */
    public function setServerParameter($key, $value)
    {
        $this->subject->setServerParameter($key, $value);
    }

    /**
     * Gets single server parameter for specified key.
     *
     * @param string $key     A key of the parameter to get
     * @param string $default A default value when key is undefined
     *
     * @return string A value of the parameter
     */
    public function getServerParameter($key, $default = '')
    {
        return $this->subject->getServerParameter($key, $default);
    }

    /**
     * Returns the History instance.
     *
     * @return History A History instance
     *
     * @api
     */
    public function getHistory()
    {
        return $this->subject->getHistory();
    }

    /**
     * Returns the CookieJar instance.
     *
     * @return CookieJar A CookieJar instance
     *
     * @api
     */
    public function getCookieJar()
    {
        return $this->subject->getCookieJar();
    }

    /**
     * Returns the current Crawler instance.
     *
     * @return Crawler|null A Crawler instance
     *
     * @api
     */
    public function getCrawler()
    {
        return $this->subject->getCrawler();
    }

    /**
     * Returns the current BrowserKit Response instance.
     *
     * @return Response|null A BrowserKit Response instance
     *
     * @api
     */
    public function getInternalResponse()
    {
        return $this->subject->getInternalResponse();
    }

    /**
     * Returns the current origin response instance.
     *
     * The origin response is the response instance that is returned
     * by the code that handles requests.
     *
     * @return object|null A response instance
     *
     * @see doRequest()
     *
     * @api
     */
    public function getResponse()
    {
        return $this->subject->getResponse();
    }

    /**
     * Returns the current BrowserKit Request instance.
     *
     * @return Request|null A BrowserKit Request instance
     *
     * @api
     */
    public function getInternalRequest()
    {
        return $this->subject->getInternalRequest();
    }

    /**
     * Returns the current origin Request instance.
     *
     * The origin request is the request instance that is sent
     * to the code that handles requests.
     *
     * @return object|null A Request instance
     *
     * @see doRequest()
     *
     * @api
     */
    public function getRequest()
    {
        return $this->subject->getRequest();
    }

    /**
     * Clicks on a given link.
     *
     * @param Link $link A Link instance
     *
     * @return Crawler
     *
     * @api
     */
    public function click(Link $link)
    {
        return $this->subject->click($link);
    }

    /**
     * Submits a form.
     *
     * @param Form  $form   A Form instance
     * @param array $values An array of form field values
     *
     * @return Crawler
     *
     * @api
     */
    public function submit(Form $form, array $values = [])
    {
        return $this->subject->submit($form, $values);
    }

    /**
     * Calls a URI.
     *
     * @param string $method        The request method
     * @param string $uri           The URI to fetch
     * @param array  $parameters    The Request parameters
     * @param array  $files         The files
     * @param array  $server        The server parameters (HTTP headers are referenced with a HTTP_ prefix as PHP does)
     * @param string $content       The raw body data
     * @param bool   $changeHistory Whether to update the history or not (only used internally for back(), forward(),
     *                              and reload())
     *
     * @return Crawler
     *
     * @api
     */
    public function request(
        $method,
        $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        $content = null,
        $changeHistory = true
    ) {
        return $this->subject->request(
            $method,
            $uri,
            $parameters,
            $files,
            $server,
            $content,
            $changeHistory
        );
    }

    /**
     * Filters the BrowserKit request to the origin one.
     *
     * @param Request $request The BrowserKit Request to filter
     *
     * @return object An origin request instance
     */
    protected function filterRequest(Request $request)
    {
        return $this->subject->filterRequest($request);
    }

    /**
     * Filters the origin response to the BrowserKit one.
     *
     * @param object $response The origin response to filter
     *
     * @return Response An BrowserKit Response instance
     */
    protected function filterResponse($response)
    {
        return $this->subject->filterResponse($response);
    }

    /**
     * Creates a crawler.
     *
     * This method returns null if the DomCrawler component is not available.
     *
     * @param string $uri     A URI
     * @param string $content Content for the crawler to use
     * @param string $type    Content type
     *
     * @return Crawler|null
     */
    protected function createCrawlerFromContent($uri, $content, $type)
    {
        return $this->subject->createCrawlerFromContent($uri, $content, $type);
    }

    /**
     * Goes back in the browser history.
     *
     * @return Crawler
     *
     * @api
     */
    public function back()
    {
        return $this->subject->back();
    }

    /**
     * Goes forward in the browser history.
     *
     * @return Crawler
     *
     * @api
     */
    public function forward()
    {
        return $this->subject->forward();
    }

    /**
     * Reloads the current browser.
     *
     * @return Crawler
     *
     * @api
     */
    public function reload()
    {
        return $this->subject->reload();
    }

    /**
     * Follow redirects?
     *
     * @return Crawler
     *
     * @throws \LogicException If request was not a redirect
     *
     * @api
     */
    public function followRedirect()
    {
        return $this->subject->followRedirect();
    }

    /**
     * Restarts the client.
     *
     * It flushes history and all cookies.
     *
     * @api
     */
    public function restart()
    {
        $this->subject->restart();
    }

    /**
     * Takes a URI and converts it to absolute if it is not already absolute.
     *
     * @param string $uri A URI
     *
     * @return string An absolute URI
     */
    protected function getAbsoluteUri($uri)
    {
        return $this->subject->getAbsoluteUri($uri);
    }
}
