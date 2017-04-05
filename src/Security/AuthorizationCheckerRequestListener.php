<?php declare(strict_types = 1);
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace KleijnWeb\SwaggerBundle\Security;


use KleijnWeb\PhpApi\Descriptions\Description\Repository;
use KleijnWeb\SwaggerBundle\EventListener\Request\RequestMeta;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class AuthorizationCheckerRequestListener
{
    const ATTRIBUTE = 'swagger.request';

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var Repository
     */
    private $documentRepository;

    /**
     * @var bool
     */
    private $matchUnsecured;

    /**
     * @param Repository                    $documentRepository
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param bool                          $matchUnsecured
     */
    public function __construct(Repository $documentRepository, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, bool $matchUnsecured)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage         = $tokenStorage;
        $this->documentRepository   = $documentRepository;
        $this->matchUnsecured       = $matchUnsecured;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        /**
         * If there is no token, the checker will fail. When match_unsecured is turned off,
         * it is assumed that the security definitions are properly set up, so we will ignore
         * unauthenticated requests for unsecured operations
         */
        if ($this->matchUnsecured === false && null === $this->tokenStorage->getToken()) {
            // TODO These next 2 statements are duplicated about 3 times now
            $description = $this->documentRepository
                ->get($request->attributes->get(RequestMeta::ATTRIBUTE_URI));

            $operation = $description
                ->getPath($request->attributes->get(RequestMeta::ATTRIBUTE_PATH))
                ->getOperation($request->getMethod());

            if (!$operation->isSecured()) {
                return;
            }
        }

        if (!$this->authorizationChecker->isGranted(self::ATTRIBUTE, $request)) {
            throw new AccessDeniedException();
        }
    }
}