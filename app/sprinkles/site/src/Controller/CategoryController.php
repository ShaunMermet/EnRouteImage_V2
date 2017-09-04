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
use UserFrosting\Sprinkle\Site\Sprunje\ImgCategoriesSprunje;
use UserFrosting\Sprinkle\Site\Sprunje\SegCategorySprunje;
use UserFrosting\Sprinkle\Site\Model\ImgCategories;
use UserFrosting\Sprinkle\Site\Model\SegCategory;
use UserFrosting\Sprinkle\Site\Model\Set;
use UserFrosting\Sprinkle\Site\Model\SegSet;

/**
 * Controller class for category-related requests.
 *
 * @author 
 * @see 
 */
class CategoryController extends SimpleController
{

    /**
     * Returns all categories.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getAllCategory($request, $response, $args)
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

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_label')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

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
        $validSet = [];
        foreach ($sets as $set) {
            array_push($validSet, $set->id);
        }      
        $cats = ImgCategories::whereIn('set_id', $validSet)
                    ->with('set')
                    ->get();
        $result = $cats->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }
    /**
     * Returns all categories.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getAllCategoryNoAuth($request, $response, $args)
    {
        $validGroup = [1];
        
        $sets = Set::whereIn('group_id', $validGroup)
                    ->with('group')
                    ->get();
        $validSet = [];
        foreach ($sets as $set) {
            array_push($validSet, $set->id);
        }      
        $cats = ImgCategories::whereIn('set_id', $validSet)
                    ->with('set')
                    ->get();
        $result = $cats->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }
    /**
     * Returns all segmetation categories.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getAllSegCategory($request, $response, $args)
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

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_label')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

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
        $validSet = [];
        foreach ($sets as $set) {
            array_push($validSet, $set->id);
        }      
        $cats = SegCategory::whereIn('set_id', $validSet)
                    ->with('set')
                    ->get();
        $result = $cats->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Edit a category.
     *
     * This page requires authentication.
     * Request type: PUT
     */
    public function editCategory($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_catEdit')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        // Get PUT parameters: (name, slug, icon, description)
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);

        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);
        
        if (!empty($data))
        {
            error_log(print_r($data,true));
            
            // Set autocommit to off
            mysqli_autocommit($db,FALSE);

            $mode = mysqli_real_escape_string($db,($data->mode));
            $catId = mysqli_real_escape_string($db,($data->catId));
            $catText = mysqli_real_escape_string($db,($data->catText));
            $catColor = "#".mysqli_real_escape_string($db,($data->catColor));
            $catSetId = mysqli_real_escape_string($db,($data->catSetId));

            if ($mode == "CREATE"){
                if ($data->catText == ""){
                    error_log ("no label");
                    echo "FAIL";
                    exit;
                    
                }
                $sql = "INSERT INTO `labelimgcategories`
                        (`Category`, `Color`, `set_id`) 
                VALUES ('$catText','$catColor','$catSetId')";
                
            }else if ($mode == "EDIT"){
                if(intval($catId) < 1){
                    error_log ("wrong ID");
                    echo "FAIL";
                    exit;
                }
                $sql = "UPDATE `labelimgcategories` SET `Category`='$catText',`Color`='$catColor',`set_id`='$catSetId' WHERE id = '$catId'" ;
            }else if ($mode == "DELETE"){
                if(intval($catId) < 1){
                    error_log ("wrong ID");
                    echo "FAIL";
                    exit;
                }
                $sql = "DELETE FROM `labelimgcategories` WHERE `id` = '$catId'" ;
            }else{
                error_log ("wrong mode");
                echo "FAIL";
            }
            if ($db->query($sql) === TRUE) {
                error_log ("Sql success");
                echo ("SUCCESS");
            } else {
                error_log ("Error: " . $sql . "<br>" . $db->error);
                echo("FAIL");
            }
            // Commit transaction
            mysqli_commit($db);
            
        }
        else // $_POST is empty.
        {
            error_log ("No data");
            echo "FAIL";
        }
    }

    /**
     * Edit a segCategory.
     *
     * This page requires authentication.
     * Request type: PUT
     */
    public function editSegCategory($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_catEdit')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        // Get PUT parameters: (name, slug, icon, description)
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);

        $mode = $data->mode;
        $catId = $data->catId;
        $catText = $data->catText;
        $catColor = "#".$data->catColor;
        $catSetId = $data->catSetId;

        if ($mode == "CREATE"){
            if ($data->catText == ""){
                error_log ("no label");
                echo "FAIL";
                exit;
            }
            
            //TODO Insert
            $cat = new SegCategory;
            $cat->Category = $catText;
            $cat->Color = $catColor;
            $cat->set_id = $catSetId;
            $cat->save();
            
        }else if ($mode == "EDIT"){
            if(intval($catId) < 1){
                error_log ("wrong ID");
                echo "FAIL";
                exit;
            }
            $cat = SegCategory::where('id', $catId)->first();
            $cat->Category = $catText;
            $cat->Color = $catColor;
            $cat->set_id = $catSetId;
            $cat->save();
        }else if ($mode == "DELETE"){
            if(intval($catId) < 1){
                error_log ("wrong ID");
                echo "FAIL";
                exit;
            }
            $cat = SegCategory::where('id', $catId)->first();
            $cat->delete();
            
        }else{
            error_log ("wrong mode");
            echo "FAIL";
        }
    }
}
