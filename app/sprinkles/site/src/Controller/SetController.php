<?php
/**
 * 
 *
 * @link      
 * @copyright 
 * @license   
 */
namespace UserFrosting\Sprinkle\Site\Controller;



use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Site\Model\Set;
use UserFrosting\Sprinkle\Site\Model\SegSet;

/**
 * Controller class for category-related requests.
 *
 * @author 
 * @see 
 */
class SetController extends SimpleController
{
	/**
     * Returns list of my sets.
     *
     * This function requires authentication.
     * Request type: GET
     */
    public function getMySets($request, $response, $args)
    {
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
        $sets = Set::whereIn('group_id', $validGroup)
        			->with('group')
                    ->get();
        
        $result = $sets->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        //return $sprunje->toResponse($response);
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }
    /**
     * Returns list of my segsets.
     *
     * This function requires authentication.
     * Request type: GET
     */
    public function getMySegSets($request, $response, $args)
    {
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
        $sets = SegSet::whereIn('group_id', $validGroup)
                    ->with('group')
                    ->get();
        
        $result = $sets->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        //return $sprunje->toResponse($response);
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }
    public function getMySetsNA($request, $response, $args)
    {
        $validGroup = [1];
        $sets = Set::whereIn('group_id', $validGroup)
                    ->with('group')
                    ->get();
        
        $result = $sets->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        //return $sprunje->toResponse($response);
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }



	/**
     * Edit a set.
     *
     * This function requires authentication.
     * Request type: PUT
     */
    public function editSet($request, $response, $args)
    {
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
        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_setEdit')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        // Get PUT parameters: (name, slug, icon, description)
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);

        $mode = $data->mode;
        $setId = $data->setId;
        $setName = $data->setName;
        $setGroup = $data->setGroup;

        if ($mode == "CREATE"){
            if ($data->setName == ""){
                error_log ("no label");
                echo "FAIL";
                exit;
            }
            
            //TODO Insert
            $set = new Set;
            $set->name = $setName;
            $set->group_id = $setGroup;
            $set->save();
            
        }else if ($mode == "EDIT"){
            if(intval($setId) < 1){
                error_log ("wrong ID");
                echo "FAIL";
                exit;
            }
            $set = Set::where('id', $setId)->first();
            $set->name = $setName;
            $set->group_id = $setGroup;
            $set->save();
        }else if ($mode == "DELETE"){
            if(intval($setId) < 1){
                error_log ("wrong ID");
                echo "FAIL";
                exit;
            }
            $set = Set::where('id', $setId)->first();
            $set->delete();
            
        }else{
            error_log ("wrong mode");
            echo "FAIL";
        }
    }

    /**
     * Edit a set.
     *
     * This function requires authentication.
     * Request type: PUT
     */
    public function editSegSet($request, $response, $args)
    {
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
        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_setEdit')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        // Get PUT parameters: (name, slug, icon, description)
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);

        $mode = $data->mode;
        $setId = $data->setId;
        $setName = $data->setName;
        $setGroup = $data->setGroup;

        if ($mode == "CREATE"){
            if ($data->setName == ""){
                error_log ("no label");
                echo "FAIL";
                exit;
            }
            
            //TODO Insert
            $set = new SegSet;
            $set->name = $setName;
            $set->group_id = $setGroup;
            $set->save();
            
        }else if ($mode == "EDIT"){
            if(intval($setId) < 1){
                error_log ("wrong ID");
                echo "FAIL";
                exit;
            }
            $set = SegSet::where('id', $setId)->first();
            $set->name = $setName;
            $set->group_id = $setGroup;
            $set->save();
        }else if ($mode == "DELETE"){
            if(intval($setId) < 1){
                error_log ("wrong ID");
                echo "FAIL";
                exit;
            }
            $set = SegSet::where('id', $setId)->first();
            $set->delete();
            
        }else{
            error_log ("wrong mode");
            echo "FAIL";
        }
    }
}