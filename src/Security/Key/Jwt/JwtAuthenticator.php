<?php
/*
 * This file is part of the KleijnWeb\SwaggerBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\SwaggerBundle\Security\Key\Jwt;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Firebase\JWT\JWT;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class JwtAuthenticator implements SimplePreAuthenticatorInterface
{
    /**
     * The exp (expiration time) claim identifies the expiration time on or after which the token MUST NOT
     * be accepted for processing. The processing of the exp claim requires that the current date/time MUST
     * be before the expiration date/time listed in the exp claim. Implementers MAY provide for some small leeway,
     * usually no more than a few minutes, to account for clock skew.
     */
    const CLAIM_NAME_EXP = 'exp';

    /**
     * he nbf (not before) claim identifies the time before which the token MUST NOT be accepted for processing.
     * The processing of the nbf claim requires that the current date/time MUST be after or equal to the not-before
     * date/time listed in the nbf claim. Implementers MAY provide for some small leeway,
     * usually no more than a few minutes, to account for clock skew.
     */
    const CLAIM_NAME_NBF = 'nbf';

    /**
     * The iat (issued at) claim identifies the time at which the JWT was issued.
     * This claim can be used to determine the age of the token.
     */
    const CLAIM_NAME_IAT = 'iat';

    /**
     * The iss (issuer) claim identifies the principal that issued the JWT.
     * The processing of this claim is generally application specific. The iss value is case sensitive.
     */
    const CLAIM_NAME_ISS = 'iss';

    /**
     * The aud (audience) claim identifies the audience that the JWT is intended for. The principal intended to
     * process the JWT MUST be identified with the value of the audience claim. If the principal processing the
     * claim does not identify itself with the identifier in the aud claim value then the JWT MUST be rejected.
     * The interpretation of the audience value is generally application specific. The aud value is case sensitive.
     */
    const CLAIM_NAME_AUD = 'aud';

    /**
     * The prn (principal) claim identifies the subject of the JWT.
     * The processing of this claim is generally application specific.
     * The prn value is case sensitive.
     */
    const CLAIM_NAME_PRN = 'prn';

    /**
     * The jti (JWT ID) claim provides a unique identifier for the JWT. The identifier value MUST be assigned
     * in a manner that ensures that there is a negligible probability that the same value will be accidentally
     * assigned to a different data object. The jti claim can be used to prevent the JWT from being replayed.
     * The jti value is case sensitive.
     */
    const CLAIM_NAME_JTI = 'jti';

    /**
     * The typ (type) claim is used to declare a type for the contents of this JWT Claims Set.
     * The typ value is case sensitive.
     */
    const CLAIM_NAME_TYP = 'typ';

    /**
     * @see http://openid.net/specs/draft-jones-json-web-token-07.html#rfc.section.4.1
     *
     * @var array
     */
    private static $knownClaims = [
        self::CLAIM_NAME_EXP,
        self::CLAIM_NAME_NBF,
        self::CLAIM_NAME_IAT,
        self::CLAIM_NAME_ISS,
        self::CLAIM_NAME_AUD,
        self::CLAIM_NAME_PRN,
        self::CLAIM_NAME_JTI,
        self::CLAIM_NAME_TYP
    ];

    /**
     * @var array
     */
    private $requiredClaims = [
        self::CLAIM_NAME_AUD
    ];

    /**
     * @var string
     */
    private $keyName;

    /**
     * @var string
     */
    private $in = 'header';

    /**
     * @var array|string
     */
    private $keys;

    /**
     * @var int
     */
    private $leeway = 10;

    /**
     * @param array|string $keys
     * @param array        $requiredClaims
     */
    public function __construct($keys, array $requiredClaims = null)
    {
        if (empty($keys)) {
            throw new \InvalidArgumentException("No keys configured");
        }
        if ($requiredClaims) {
            $this->requiredClaims = $requiredClaims;
        }
        $this->keys = $keys;
    }

    /**
     * @param Request $request
     * @param string  $providerKey
     *
     * @return PreAuthenticatedToken
     */
    public function createToken(Request $request, $providerKey)
    {
        $key = null;
        if ($this->in === 'header') {
            $key = $request->headers->get($this->keyName);

            if (0 === strpos($key, 'Bearer ')) {
                $key = substr($key, 7);
            }
        }
        if ($this->in === 'query') {
            $key = $request->query->get($this->keyName);
        }

        if (!$key) {
            throw new BadCredentialsException('No API key found');
        }

        try {
            $leewayBefore = JWT::$leeway;
            JWT::$leeway = $this->leeway;
            $claims = (array)JWT::decode($key, $this->keys, ['HS256']);
            JWT::$leeway = $leewayBefore;
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid key', 0, $e);
        }
        $this->validateClaims($claims, $providerKey);

        return new PreAuthenticatedToken('anon.', $claims, $providerKey);
    }

    /**
     * @param array  $claims
     * @param string $providerKey
     *
     * @throws AuthenticationException
     */
    public function validateClaims(array $claims, $providerKey)
    {
        $missingClaims = array_diff_key(array_flip($this->requiredClaims), $claims);
        if (count($missingClaims) > 0) {
            throw new AuthenticationException('Missing claims:' . implode(', ', $missingClaims));
        }

        if (isset($claims[self::CLAIM_NAME_AUD]) && ($providerKey !== $claims[self::CLAIM_NAME_AUD])) {
            throw new AuthenticationException(
                "JWT aud claim should match providerKey (providerKey: "
                . "{$providerKey}, aud: {$claims[self::CLAIM_NAME_AUD]})"
            );
        }
        $unknownClaims = array_diff_key($claims, array_flip(self::$knownClaims));
        if (count($unknownClaims) > 0) {
            throw new AuthenticationException('Unknown claims:' . implode(', ', $missingClaims));
        }
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

    /**
     * @param array $claims
     *
     * @return string
     */
    public function getUsername(array $claims)
    {
        if (!isset($claims[JwtAuthenticator::CLAIM_NAME_PRN])) {
            throw new \InvalidArgumentException(
                "Cannot extract username: missing principle claim"
            );
        }

        return $claims[JwtAuthenticator::CLAIM_NAME_PRN];
    }
}
