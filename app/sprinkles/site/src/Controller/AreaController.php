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
use UserFrosting\Sprinkle\Site\Sprunje\SegAreaSprunje;
use UserFrosting\Sprinkle\Site\Model\ImgArea;
use UserFrosting\Sprinkle\Site\Model\ImgLinks;
use UserFrosting\Sprinkle\Site\Model\SegArea;
use UserFrosting\Sprinkle\Site\Model\SegImage;

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
     * Get the segareas corresponding to sprunje filter.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getSegAreaSprunje($request, $response, $args)
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

        $sprunje = new SegAreaSprunje($classMapper, $params);

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

        $user = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->first();

        $count = [];
        
        $count['rejectedImg'] = $user->stats_rejected;
        
        $count['validatedImg'] = $user->stats_validated;

        $count['pendingImg'] = ImgLinks::whereHas('areas', function ($query) use ($currentUser){
                                $query->where('user', '=', $currentUser->id);
                                })
                                ->where ('state', '=', 2)
                                ->where(function ($imgLinks) use ($validGroup){
                                $imgLinks->whereIn('group', $validGroup)
                                        ->orWhereNull('group');
                                })
                                ->count();

        
        $count['segRejectedImg'] = $user->stats_rejected_seg;
        
        $count['segValidatedImg'] = $user->stats_validated_seg;

        $count['segPendingImg'] = SegImage::whereHas('areas', function ($query) use ($currentUser){
                                    $query->where('user', '=', $currentUser->id);
                                })
                                ->where ('state', '=', 2)
                                ->where(function ($imgLinks) use ($validGroup){
                                $imgLinks->whereIn('group', $validGroup)
                                        ->orWhereNull('group');
                                })
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
     * Returns all (not deleted) seg areas of images on provided ids.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getAreasByIds($request, $response, $args)
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

        // GET parameters
        $params = $request->getQueryParams();
        if (!array_key_exists("ids",$params)) $params["ids"] = [];

        $imgAreas = ImgArea::with('category')
                            ->whereIn('source', $params["ids"])
                            ->get();
        
        $result = $imgAreas->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Returns all (not deleted) seg areas of images on provided ids.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getSegAreasByIds($request, $response, $args)
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

        // GET parameters
        $params = $request->getQueryParams();

       $imgAreas = SegArea::with('category')
                            ->whereIn('source', $params["ids"])
                            ->get();
        
        foreach ($imgAreas as $imgArea) {
            $imgArea->data = unserialize($imgArea->data);
        }
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

        if (!empty($data))
        {
            $this->deleteAreas($data->dataSrc,TRUE);
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
            $targetImg = ImgLinks::where('id', $data->dataSrc)->first();
            $targetImg->state = 2;
            $targetImg->save();
        }
        else // $_POST is empty.
        {
            error_log("No data") ;
        }
    }

    /**
     * Saves segAreas given.
     *
     * This page requires authentication.
     * Request type: PUT
     */
    public function saveSegAreas($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_label')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }


        // Get parameters
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);

        $checkImage = SegImage::where('id', $data->dataSrc)
                    ->where('updated_at',$data->updated)
                    ->count();
        if($checkImage != 1){
            return $response->withJson([], 408, JSON_PRETTY_PRINT);
        }

        if (!empty($data))
        {
            $this->deleteSegAreas($data->dataSrc,TRUE);
            $areas= $data->areas;
            foreach ($areas as $num => $area) {//for each rectangle
                $SegArea = new SegArea;
                $SegArea->source = $data->dataSrc;
                $SegArea->areaType = $area->type;
                $SegArea->data = serialize($area->points);//$array2 = unserialize($array);
                if($currentUser){
                    $SegArea->user = $currentUser->id;
                }else{
                    $SegArea->user = 0;
                }
                $SegArea->save();
            }
            $targetImg = SegImage::where('id', $data->dataSrc)->first();
            $targetImg->state = 2;
            $targetImg->save();
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
            $targetImg = ImgLinks::where('id', $data->dataSrc)->first();
            $targetImg->state = 2;
            $targetImg->save();
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
            $user = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->first();
            $state = 4;
            if ($validated == 1){
                $state = 3;
                $user->stats_validated = $user->stats_validated+1;
            }else{
                //$this->deleteAreas($source,FALSE);
                $state = 4;
                $user->stats_rejected = $user->stats_rejected+1;
            }
            $user->save();
            $sql = "UPDATE `labelimglinks` SET `state` = $state ,`validated_at`= NOW() WHERE `labelimglinks`.`id` = '$source'";
            if ($db->query($sql) === TRUE) {
                
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

    /**
     * Update a segarea and validate it.
     *
     * This page requires authentication.
     * Request type: PUT
     */
    public function segAreaEvaluate($request, $response, $args)
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

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_validate')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        // Get PUT parameters: (name, slug, icon, description)
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);
        $checkImage = SegImage::where('id', $data->dataSrc)
                    ->where('updated_at',$data->updated)
                    ->count();
        if($checkImage != 1){
            return $response->withJson([], 408, JSON_PRETTY_PRINT);
        }

        if($data->validated == 0){
            //$this->deleteSegAreas($data->dataSrc,FALSE);
        }

        $segimg = SegImage::where('id', $data->dataSrc)->first();
        $user = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->first();
        if($data->validated == 0){
            $segimg->state = 4;
            $user->stats_rejected_seg = $user->stats_rejected_seg+1;
        }
        else{
            $segimg->state = 3;
            $user->stats_validated_seg = $user->stats_validated_seg+1;
        }
        $segimg->save();
        $user->save();
    }

    private function deleteAreas($source = NULL,$forcedelete){
        if(!is_null($source)){
            if($forcedelete)
                $rowsToDelete = ImgArea::where('source', '=', $source)->forceDelete();
            else
                $rowsToDelete = ImgArea::where('source', '=', $source)->delete();
        }
    }
    private function deleteSegAreas($source = NULL,$forcedelete){
        if(!is_null($source)){
            if($forcedelete)
                $rowsToDelete = SegArea::where('source', '=', $source)->forceDelete();
            else
                $rowsToDelete = SegArea::where('source', '=', $source)->delete();
        }
    }
}
