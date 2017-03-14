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
use UserFrosting\Sprinkle\Site\Sprunje\ImgAreaSprunje;
use UserFrosting\Sprinkle\Site\Model\ImgArea;
use UserFrosting\Sprinkle\Site\Model\ImgLinks;

/**
 * Controller class for category-related requests.
 *
 * @author 
 * @see 
 */
class AreaController extends SimpleController
{
    /**
     * Get the areas corresponding to sprunje filter.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getAreaSprunje($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'get_area')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $sprunje = new ImgAreaSprunje($classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Get the areas corresponding to user given
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getAreaUserStats($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_account_settings')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $count = [];
        
        $count['deletedArea'] = ImgArea::onlyTrashed()
                                ->where('user', '=', $currentUser->id)
                                ->count();
        
        $count['validatedArea'] = ImgArea::where('user', '=', $currentUser->id)
                                ->joinImglinks()
                                ->where('validated', '=',1)
                                ->count();

        $count['pendingArea'] = ImgArea::where('user', '=', $currentUser->id)
                                ->joinImglinks()
                                ->where('validated', '=',0)
                                ->count();


        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($count, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Returns all (not deleted) areas of all images.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getAllAreas($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_validate')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }


        $imgAreas = ImgArea::with('category')
                            ->get();
        

        $result = $imgAreas->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Saves areas given.
     *
     * This page requires authentication.
     * Request type: PUT
     */
    public function saveAreas($request, $response, $args)
    {
        error_log("saveAreas request3");
               // error_log( print_r($request, TRUE) );
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

        // Get parameters
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);

        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);

        if (!empty($data))
        {
            error_log("Data sended to server\n") ;
            
            $source = mysqli_real_escape_string($db,($data->dataSrc));
            
            $rects= $data->rects;
            foreach ($rects as $num => $rect) {//for each rectangle
                
                $rectType = mysqli_real_escape_string($db,($rect->type));
                $rectLeft = mysqli_real_escape_string($db,($rect->rectLeft));
                $rectTop = mysqli_real_escape_string($db,($rect->rectTop));
                $rectRight = mysqli_real_escape_string($db,($rect->rectRight));
                $rectBottom = mysqli_real_escape_string($db,($rect->rectBottom));
                $rectUSer = $currentUser->id;
                $sql = "SELECT * FROM 
                `labelimgarea` lia WHERE 
                lia.source='$source' AND 
                lia.rectType='$rectType' AND 
                lia.rectLeft='$rectLeft' AND 
                lia.rectTop='$rectTop' AND 
                lia.rectRight='$rectRight' AND 
                lia.rectBottom='$rectBottom';";
                $result = $db->query($sql);
                if ($result->num_rows > 0) {
                    echo "row was already created";
                } else {
                    $sql = "
                    INSERT INTO labelimgarea (source, rectType, rectLeft,rectTop,rectRight,rectBottom,user)
                    VALUES ('$source','$rectType','$rectLeft','$rectTop','$rectRight','$rectBottom','$rectUSer')";
                    if ($db->query($sql) === TRUE) {
                        echo "New record created successfully";
                    } else {
                        echo "Error: " . $sql . "<br>" . $db->error;
                    }
                }
            }
            $db->close();
        }
        else // $_POST is empty.
        {
            error_log("No data") ;
        }
    }

    /**
     * Saves areas given.
     *
     * 
     * Request type: PUT
     */
    public function saveAreasNoAuth($request, $response, $args)
    {
        // Get parameters
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        if (!empty($data))
        {
           $rects= $data->rects;
            foreach ($rects as $num => $rect) {//for each rectangle
                $area = new ImgArea;
                $area->source = $data->dataSrc;
                $area->rectType = $rect->type;
                $area->rectLeft = $rect->rectLeft;
                $area->rectTop = $rect->rectTop;
                $area->rectRight = $rect->rectRight;
                $area->rectBottom = $rect->rectBottom;
                if($currentUser){
                    $area->user = $currentUser->id;
                }else{
                    $area->user = 0;
                }
                $area->save();
            }
        }
        else // $_POST is empty.
        {
            error_log("No data") ;
        }
    }

    /**
     * Returns update an area and validate it.
     *
     * This page requires authentication.
     * Request type: PUT
     */
    public function areaEvaluate($request, $response, $args)
    {
        error_log("in areaEvaluate");
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
        if (!$authorizer->checkAccess($currentUser, 'uri_validate')) {
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
            
            $source = mysqli_real_escape_string($db,($data->dataSrc));
            $validated = mysqli_real_escape_string($db,($data->validated));
            error_log($validated);
            if ($validated == 1){

            }else{
                $this->deleteArea($source,$db);
            }
            $sql = "UPDATE `labelimglinks` SET `validated` = $validated ,`validated_at`= NOW() WHERE `labelimglinks`.`id` = '$source'";
            if ($db->query($sql) === TRUE) {
                error_log("record done") ;
            } else {
                error_log("Error: " . $sql . "<br>" . $db->error) ;
            }
            $db->close();
        }
        else // $_POST is empty.
        {
            error_log("No data") ;
        }
    }

    private function deleteArea($source = NULL,$db){
        if(!is_null($source)){
            $rowsToDelete = ImgArea::where('source', '=', $source)->delete();
        }
    }
}
