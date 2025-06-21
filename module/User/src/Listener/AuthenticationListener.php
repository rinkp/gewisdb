<?php

declare(strict_types=1);

namespace User\Listener;

use InvalidArgumentException;
use Laminas\Authentication\AuthenticationService;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ResponseInterface;
use LogicException;
use User\Adapter\ApiPrincipalAdapter;
use User\Service\ApiAuthenticationService;

use function strlen;
use function substr;

final class AuthenticationListener
{
    // Defining the authentication types
    public const AUTH_NONE = 'none';
    public const AUTH_DBUSER = 'dbuser';
    public const AUTH_API = 'api';

    public function __construct(
        private readonly AuthenticationService $authService,
        private readonly ApiAuthenticationService $apiAuthService,
        private readonly ApiPrincipalAdapter $apiPrincipalAdapter,
    ) {
    }

    public function __invoke(MvcEvent $e): ?ResponseInterface
    {
        if (MvcEvent::EVENT_ROUTE !== $e->getName()) {
            throw new InvalidArgumentException(
                'Expected MvcEvent of type ' . MvcEvent::EVENT_ROUTE . ', got ' . $e->getName(),
            );
        }

        $match = $e->getRouteMatch();
        if (null === $match) {
            throw new LogicException('Did not match any route after being routed');
        }

        switch ($match->getParam('auth_type', self::AUTH_DBUSER)) {
            case self::AUTH_DBUSER:
                return $this->dbuserAuth($e);

            case self::AUTH_API:
                return $this->apiAuth($e);

            case self::AUTH_NONE:
                return null;

            default:
                throw new InvalidArgumentException(
                    'Authentication type was set to unknown type ' . $match->getParam('auth_type'),
                );
        }
    }

    /**
     * Handle authentication for users
     */
    private function dbuserAuth(MvcEvent $e): ?ResponseInterface
    {
        if ($this->authService->hasIdentity()) {
            // user is logged in, just continue
            return null;
        }

        // If this is a HTTP request, we redirect the user to the login page
        $response = $e->getResponse();
        if ($response instanceof HttpResponse) {
            $response->getHeaders()->addHeaderLine('Location', '/login');
            $response->setStatusCode(302);
        }

        // Return $response will prevent output, but we also stop other listeners when we need to logon first
        $e->stopPropagation();

        return $response;
    }

    /**
     * Handle authentication for api tokens
     */
    private function apiAuth(MvcEvent $e): ?ResponseInterface
    {
        if ($e->getRequest()->getHeaders()->has('Authorization')) {
            // This is an API call, we do this on every request
            $token = $e->getRequest()->getHeaders()->get('Authorization')->getFieldValue();
            $this->apiPrincipalAdapter->setCredentials(substr($token, strlen('Bearer ')));
            $result = $this->apiAuthService->authenticate($this->apiPrincipalAdapter);
            if ($result->isValid()) {
                return null;
            }
        }

        // If this is a HTTP request, we add authentication headers
        $response = $e->getResponse();
        if ($response instanceof HttpResponse) {
            $response->getHeaders()->addHeaderLine('WWW-Authenticate', 'Bearer realm="/api"');
            $response->setStatusCode(401);
        }

        $e->stopPropagation();

        return $response;
    }
}
