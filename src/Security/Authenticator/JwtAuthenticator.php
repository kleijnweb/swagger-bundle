<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Security\Authenticator;

use KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\JwtKey;
use KleijnWeb\SwaggerBundle\Security\Authenticator\JwtAuthenticator\JwtToken;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JwtAuthenticator implements SimplePreAuthenticatorInterface
{
    /**
     * @var JwtKey[]
     */
    private $keys = [];

    /**
     * @param array $keyOptions
     */
    public function __construct(array $keyOptions)
    {
        foreach ($keyOptions as $name => $options) {
            $this->keys[$name] = new JwtKey($options);
        }
    }

    /**
     * @param string $id
     *
     * @return JwtKey
     */
    public function getKeyById($id)
    {
        if ($id) {
            if (!isset($this->keys[$id])) {
                throw new AuthenticationException("Unknown 'kid' $id");
            }

            return $this->keys[$id];
        }
        if (count($this->keys) > 1) {
            throw new AuthenticationException("Missing 'kid'");
        }

        return current($this->keys);
    }

    /**
     * @param array $claims
     *
     * @return string
     */
    public function getUsername(array $claims)
    {
        if (!isset($claims['prn'])) {
            throw new AuthenticationException(
                "Cannot extract username: missing principle claim"
            );
        }

        return $claims['prn'];
    }

    /**
     * @param Request $request
     * @param string  $providerKey
     *
     * @return PreAuthenticatedToken
     */
    public function createToken(Request $request, $providerKey)
    {
        $tokenString = $request->headers->get('Authorization');

        if (0 === strpos($tokenString, 'Bearer ')) {
            $tokenString = substr($tokenString, 7);
        }

        if (!$tokenString) {
            throw new BadCredentialsException('No API key found');
        }

        try {
            $token = new JwtToken($tokenString);
            $key = $this->getKeyById($token->getKeyId());
            $key->validateToken($token);
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid key', 0, $e);
        }

        return new PreAuthenticatedToken('anon.', $token->getClaims(), $providerKey);
    }

    /**
     * @param TokenInterface        $token
     * @param UserProviderInterface $userProvider
     * @param string                $providerKey
     *
     * @return PreAuthenticatedToken
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $claims = $token->getCredentials();
        $user = $userProvider->loadUserByUsername($this->getUsername($claims));

        return new PreAuthenticatedToken($user, $claims, $providerKey, $user->getRoles());
    }

    /**
     * @param TokenInterface $token
     * @param string         $providerKey
     *
     * @return bool
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }
}
