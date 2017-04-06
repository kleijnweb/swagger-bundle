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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class RbacRequestVoter implements VoterInterface
{
    /**
     * @var Repository
     */
    private $documentRepository;

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * @param Repository                     $documentRepository
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(Repository $documentRepository, AccessDecisionManagerInterface $decisionManager)
    {
        $this->documentRepository = $documentRepository;
        $this->decisionManager    = $decisionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $request, array $attributes)
    {
        $vote = VoterInterface::ACCESS_ABSTAIN;

        if (!$request instanceof Request) {
            return $vote;
        }

        // Bail early
        $supported = false;
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute)) {
                $supported = true;
            }
        }
        if (!$supported) {
            return $vote;
        }

        if (!$requestMeta = RequestMeta::fromRequest($request, $this->documentRepository)) {
            return $vote;
        }

        // If the operation is secured, IS_AUTHENTICATED_FULLY unless overridden by x-rbac
        if ($requestMeta->getOperation()->isSecured()) {
            $roles = ['IS_AUTHENTICATED_FULLY'];
        } else {
            // Otherwise, test against IS_AUTHENTICATED_ANONYMOUSLY
            $roles = ['IS_AUTHENTICATED_ANONYMOUSLY'];
        }

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            if ($rbac = $requestMeta->getOperation()->getExtension('rbac')) {
                $roles = $this->normalizeRoleNames($rbac);
            }

            if ($this->decisionManager->decide($token, $roles)) {
                $vote = VoterInterface::ACCESS_GRANTED;
            } else {
                $vote = VoterInterface::ACCESS_DENIED;
            }
        }

        return $vote;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return $attribute === RequestAuthorizationListener::ATTRIBUTE;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return false;
    }

    /**
     * @param array|string $roleNames
     * @return array
     */
    private function normalizeRoleNames($roleNames): array
    {
        $roleNames = !is_array($roleNames) ? [$roleNames] : $roleNames;
        foreach ($roleNames as &$roleName) {
            $roleName = strtoupper($roleName);
            if (0 !== strpos($roleName, 'ROLE_')) {
                $roleName = "ROLE_$roleName";
            }
        }

        return $roleNames;
    }
}
