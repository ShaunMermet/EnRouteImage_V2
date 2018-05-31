<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Admin\ServicesProvider;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * Registers services for the admin sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ServicesProvider
{
    /**
     * Register UserFrosting's admin services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
        /**
         * Extend the 'classMapper' service to register sprunje classes.
         *
         * Mappings added: 'activity_sprunje', 'group_sprunje', 'permission_sprunje', 'role_sprunje', 'user_sprunje'
         */
        $container->extend('classMapper', function ($classMapper, $c) {
            $classMapper->setClassMapping('activity_sprunje', 'UserFrosting\Sprinkle\Admin\Sprunje\ActivitySprunje');
            $classMapper->setClassMapping('group_sprunje', 'UserFrosting\Sprinkle\Admin\Sprunje\GroupSprunje');
            $classMapper->setClassMapping('permission_sprunje', 'UserFrosting\Sprinkle\Admin\Sprunje\PermissionSprunje');
            $classMapper->setClassMapping('permission_user_sprunje', 'UserFrosting\Sprinkle\Admin\Sprunje\PermissionUserSprunje');
            $classMapper->setClassMapping('role_sprunje', 'UserFrosting\Sprinkle\Admin\Sprunje\RoleSprunje');
            $classMapper->setClassMapping('user_sprunje', 'UserFrosting\Sprinkle\Admin\Sprunje\UserSprunje');
            $classMapper->setClassMapping('user_permission_sprunje', 'UserFrosting\Sprinkle\Admin\Sprunje\UserPermissionSprunje');
            return $classMapper;
        });

        /**
         * Returns a callback that handles setting the `UF-Redirect` header after a successful login.
         *
         * Overrides the service definition in the account Sprinkle.
         */
        $container['redirect.onLogin'] = function ($c) {
            /**
             * This method is invoked when a user completes the login process.
             *
             * Returns a callback that handles setting the `UF-Redirect` header after a successful login.
             * @param \Psr\Http\Message\ServerRequestInterface $request  
             * @param \Psr\Http\Message\ResponseInterface      $response 
             * @param array $args
             * @return \Psr\Http\Message\ResponseInterface
             */
            return function (Request $request, Response $response, array $args) use ($c) {
                // Backwards compatibility for the deprecated determineRedirectOnLogin service
                if ($c->has('determineRedirectOnLogin')) {
                    $determineRedirectOnLogin = $c->determineRedirectOnLogin;
            
                    return $determineRedirectOnLogin($response)->withStatus(200);
                }

                /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
                $authorizer = $c->authorizer;

                $currentUser = $c->authenticator->user();

                if ($authorizer->checkAccess($currentUser, 'uri_dashboard')) {
                    return $response->withHeader('UF-Redirect', $c->router->pathFor('dashboard'));
                } elseif ($authorizer->checkAccess($currentUser, 'uri_account_settings')) {
                    return $response->withHeader('UF-Redirect', $c->router->pathFor('settings'));
                } else {
                    return $response->withHeader('UF-Redirect', $c->router->pathFor('index'));
                }
            };
        };
    }
}
