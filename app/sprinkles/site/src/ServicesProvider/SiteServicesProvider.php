<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Site\ServicesProvider;

use Birke\Rememberme\Authenticator as RememberMe;
use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard;
use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Account\Log\UserActivityDatabaseHandler;
use UserFrosting\Sprinkle\Account\Log\UserActivityProcessor;
use UserFrosting\Sprinkle\Account\Model\User;
use UserFrosting\Sprinkle\Account\Repository\PasswordResetRepository;
use UserFrosting\Sprinkle\Account\Repository\VerificationRepository;
use UserFrosting\Sprinkle\Account\Twig\AccountExtension;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Log\MixedFormatter;

/**
 * Registers services for the site sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SiteServicesProvider
{
    /**
     * Register UserFrosting's site services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {


        /**
         * Extend the 'classMapper' service to register model classes.
         *
         * Mappings added: User, Group, Role, Permission, Activity, PasswordReset, Verification
         */
        $container->extend('classMapper', function ($classMapper, $c) {
            $classMapper->setClassMapping('user', 'UserFrosting\Sprinkle\Site\Model\User');
            $classMapper->setClassMapping('user_sprunje', 'UserFrosting\Sprinkle\Site\Sprunje\UserSprunje');
            return $classMapper;
        });
        /**
         * Returns a callback that handles setting the `UF-Redirect` header after a successful login.
         */
        $container['determineRedirectOnLogin'] = function ($c) {
            return function ($response) use ($c)
            {
                /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
                $authorizer = $c->authorizer;

                $currentUser = $c->authenticator->user();

                if ($authorizer->checkAccess($currentUser, 'uri_dashboard')) {
                    return $response->withHeader('UF-Redirect', $c->router->pathFor('dashboard'));
                } elseif ($authorizer->checkAccess($currentUser, 'uri_account_settings')) {
                    return $response->withHeader('UF-Redirect', $c->router->pathFor('index'));
                } else {
                    return $response->withHeader('UF-Redirect', $c->router->pathFor('index'));
                }
            };
        };

        /**
         * Authorization service.
         *
         * Determines permissions for user actions.  Extend this service to add additional access condition callbacks.
         */
        $container['authorizer'] = function ($c) {
            $config = $c->config;

            // Default access condition callbacks.  Add more in your sprinkle by using $container->extend(...)
            $callbacks = [
                /**
                 * Unconditionally grant permission - use carefully!
                 * @return bool returns true no matter what.
                 */
                'always' => function () {
                    return true;
                },

                /**
                 * Check if the specified values are identical to one another (strict comparison).
                 * @param mixed $val1 the first value to compare.
                 * @param mixed $val2 the second value to compare.
                 * @return bool true if the values are strictly equal, false otherwise.
                 */
                'equals' => function ($val1, $val2) {
                    return ($val1 === $val2);
                },

                /**
                 * Check if the specified values are numeric, and if so, if they are equal to each other.
                 * @param mixed $val1 the first value to compare.
                 * @param mixed $val2 the second value to compare.
                 * @return bool true if the values are numeric and equal, false otherwise.
                 */
                'equals_num' => function ($val1, $val2) {
                    if (!is_numeric($val1)) {
                        return false;
                    }
                    if (!is_numeric($val2)) {
                        return false;
                    }

                    return ($val1 == $val2);
                },

                /**
                 * Check if the specified user (by user_id) has a particular role.
                 *
                 * @param int $user_id the id of the user.
                 * @param int $role_id the id of the role.
                 * @return bool true if the user has the role, false otherwise.
                 */
                'has_role' => function ($user_id, $role_id) {
                    return Capsule::table('role_users')
                        ->where('user_id', $user_id)
                        ->where('role_id', $role_id)
                        ->count() > 0;
                },

                /**
                 * Check if the specified value $needle is in the values of $haystack.
                 *
                 * @param mixed $needle the value to look for in $haystack
                 * @param array[mixed] $haystack the array of values to search.
                 * @return bool true if $needle is present in the values of $haystack, false otherwise.
                 */
                'in' => function ($needle, $haystack) {
                    return in_array($needle, $haystack);
                },

                /**
                 * Check if the specified user (by user_id) is in a particular group.
                 *
                 * @param int $user_id the id of the user.
                 * @param int $group_id the id of the group.
                 * @return bool true if the user is in the group, false otherwise.
                 */
                'in_group' => function ($user_id, $group_id) {
                    error_log('In group !!!');
                    return Capsule::table('user_groups')
                        ->where('user_id', $user_id)
                        ->where('group_id', $group_id)
                        ->count() > 0;
                },

                /**
                 * Check if the specified user1 (by user_id1) share a group with user2 (user_id2).
                 *
                 * @param int $user_id1 the id of the user one.
                 * @param int $user_id2 the id of the user two.
                 * @return bool true if the users share a group, false otherwise.
                 */
                'share_group' => function ($user_id1, $user_id2) {
                    if($user_id1 == $user_id2)
                        return true;
                    return Capsule::table('user_groups')
                        ->whereIn('user_id', [$user_id1,$user_id2])
                        ->groupBy('group_id')
                        ->havingRaw('count(*) > 1')
                        ->count() > 0;
                },

                /**
                 * Check if the specified user (by user_id) is the master user.
                 *
                 * @param int $user_id the id of the user.
                 * @return bool true if the user id is equal to the id of the master account, false otherwise.
                 */
                'is_master' => function ($user_id) use ($config) {
                    // Need to use loose comparison for now, because some DBs return `id` as a string
                    return ($user_id == $config['reserved_user_ids.master']);
                },

                /**
                 * Check if all values in the array $needle are present in the values of $haystack.
                 *
                 * @param array[mixed] $needle the array whose values we should look for in $haystack
                 * @param array[mixed] $haystack the array of values to search.
                 * @return bool true if every value in $needle is present in the values of $haystack, false otherwise.
                 */
                'subset' => function ($needle, $haystack) {
                    return count($needle) == count(array_intersect($needle, $haystack));
                },

                /**
                 * Check if all keys of the array $needle are present in the values of $haystack.
                 *
                 * This function is useful for whitelisting an array of key-value parameters.
                 * @param array[mixed] $needle the array whose keys we should look for in $haystack
                 * @param array[mixed] $haystack the array of values to search.
                 * @return bool true if every key in $needle is present in the values of $haystack, false otherwise.
                 */
                'subset_keys' => function ($needle, $haystack) {
                    return count($needle) == count(array_intersect(array_keys($needle), $haystack));
                }
            ];

            $authorizer = new AuthorizationManager($c, $callbacks);
            return $authorizer;
        };
    }
}
