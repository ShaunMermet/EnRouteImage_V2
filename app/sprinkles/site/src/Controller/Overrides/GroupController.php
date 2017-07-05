<?php
/**
 * 
 *
 * @link      
 * @copyright 
 * @license   
 */
namespace UserFrosting\Sprinkle\Site\Controller\Overrides;



use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Site\Sprunje\GroupSprunje;

/**
 * Controller class for category-related requests.
 *
 * @author 
 * @see 
 */
class GroupController extends SimpleController
{
    /**
     * Returns list of my groups.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getMyGroups($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
        $authenticator = $this->ci->authenticator;
        if (!$authenticator->check()) {
            $loginPage = $this->ci->router->pathFor('login');
            return $response->withRedirect($loginPage, 400);
        }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

        $validGroup = [];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }
        $params['filters']['id'] = implode("||",$validGroup);

        $sprunje = $classMapper->createInstance('group_sprunje', $classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    

    
}
