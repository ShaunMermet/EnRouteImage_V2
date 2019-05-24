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
use UserFrosting\Sprinkle\Site\Database\Models\ImgArea;
use UserFrosting\Sprinkle\Site\Database\Models\ImgLinks;
use UserFrosting\Sprinkle\Site\Database\Models\SegArea;
use UserFrosting\Sprinkle\Site\Database\Models\SegImage;
use UserFrosting\Sprinkle\Site\Database\Models\SegMask;

/**
 * Controller class for category-related requests.
 *
 * @author 
 * @see 
 */
class AreaController extends SimpleController
{
    var $IMG_STATE_FORTAG = 1;
    var $IMG_STATE_PENDING = 2;
    var $IMG_STATE_VALIDATED = 3;
    var $IMG_STATE_REJECTED = 4;
    
    var $IMG_VALIDATION_TOTAG = 0;
    var $IMG_VALIDATION_SAVEANDTOTAG = 1;
    var $IMG_VALIDATION_SAVEANDVALID = 2;
    
    var $AREA_STATE_PENDING = 2;
    var $AREA_STATE_VALIDATED = 3;
    var $AREA_STATE_REJECTED = 4;

    var $USER_ID_UNKNOWN = -2;
    var $USER_ID_UNIVERSAL = -3;
    
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
        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

        $validGroup = ['NULL'];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }

        $count = [];

        $count["segRejectedImg"] = SegArea::onlyTrashed()
                                ->where('user', '=', $currentUser->id)
                                ->where('state', '=', 4)
                                ->count();
        $count["segValidatedImg"] = SegArea::where('user', '=', $currentUser->id)
                                ->where('state', '=', 3)
                                ->count();
        $count["segPendingImg"] = SegArea::where('user', '=', $currentUser->id)
                                ->where('state', '=', 2)
                                ->count();

        $count["rejectedArea"] = ImgArea::onlyTrashed()
                                ->where('user', '=', $currentUser->id)
                                ->where('state', '=', 4)
                                ->count();
        $count["validatedArea"] = ImgArea::where('user', '=', $currentUser->id)
                                ->where('state', '=', 3)
                                ->count();
        $count["pendingArea"] = ImgArea::where('user', '=', $currentUser->id)
                                ->where('state', '=', 2)
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
    //    $authenticator = $this->ci->authenticator;
    //    if (!$authenticator->check()) {
    //        $loginPage = $this->ci->router->pathFor('login');
    //        return $response->withRedirect($loginPage, 400);
    //    }

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
    //    $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
    //    if (!$authorizer->checkAccess($currentUser, 'uri_validate')) {
    //        $loginPage = $this->ci->router->pathFor('login');
    //       return $response->withRedirect($loginPage, 400);
    //    }

        // GET parameters
        $params = $request->getQueryParams();
        if (!array_key_exists("ids",$params)) $params["ids"] = [];

        $imgAreas = ImgArea::with('category')
                            ->whereIn('source', $params["ids"])
                            ->get();
        
        $result = $imgAreas->toArray();
        //Added editable or not info
        $result2 = [];
        foreach ($result as $area) {
            if($area["user"] == $currentUser->id || $area["user"] == $this->USER_ID_UNIVERSAL)
                $area["owned"] = 1;
            else $area["owned"] = 0;
            array_push($result2, $area);
        }
        
        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result2, 200, JSON_PRETTY_PRINT);
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

        error_log(print_r($data,true));

        $areaReceived = [];
        foreach ($data->areas as $area) {//for each area submitted
            if(!isset($area->id))$area->id = -1;
            array_push($areaReceived, $area->id);
            if($area->id > 0){
                $bddArea = ImgArea::where ('id', '=', $area->id)
                        ->first();
                if($bddArea->user == $currentUser->id && $bddArea->state == $this->AREA_STATE_PENDING){
                    $bddArea->rectType = $area->rectType;
                    $bddArea->rectLeft  = $area->rectLeft;
                    $bddArea->rectTop  = $area->rectTop;
                    $bddArea->rectRight  = $area->rectRight;
                    $bddArea->rectBottom  = $area->rectBottom;
                    $bddArea->save();
                    array_push($areaReceived, $bddArea->id);
                }
            }else if($area->id <= 0){
                //Create area
                $bddArea = new ImgArea;
                $bddArea->source = $data->dataSrc;
                $bddArea->rectType = $area->rectType;
                $bddArea->rectLeft  = $area->rectLeft;
                $bddArea->rectTop  = $area->rectTop;
                $bddArea->rectRight  = $area->rectRight;
                $bddArea->rectBottom  = $area->rectBottom;
                $bddArea->user = $currentUser->id;
                $bddArea->state = $this->AREA_STATE_PENDING;
                $bddArea->save();
                array_push($areaReceived, $bddArea->id);
            }
        }
        $prevBddAreas = ImgArea::where ('source', '=', $data->dataSrc)
                        ->where ('user', '=', $currentUser->id)
                        ->where('state', '=', $this->AREA_STATE_PENDING)
                        ->get();
        foreach ($prevBddAreas as $prevBddArea) {
            if(array_search($prevBddArea->id, $areaReceived) !== false){
                error_log("area found ".$prevBddArea->id);
                //update area in other loop
            }
            else{
                error_log("area NOT found ". $prevBddArea->id);
                $prevBddArea->forceDelete();
            }
        }
        if($data->validImage === true){
            $img = imgLinks::where('id', $data->dataSrc)->first();
            $img->state = $this->IMG_STATE_PENDING;
            $img->save();
        }   
        return;
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

        error_log("saveSegSlic");
        //error_log(print_r($data,true));
        
        error_log(print_r($data->dataSrc,true));
        $checkImage = SegImage::where('id', $data->dataSrc)
                    ->where('updated_at',$data->updated)
                    ->count();
        error_log(print_r($data->updated,true));
        if($checkImage != 1){
            error_log("checkimage != 1");
            error_log(print_r($checkimage,true));
            return $response->withJson([], 408, JSON_PRETTY_PRINT);
        }

        error_log("apres 408");

        if (!empty($data))
        {

            $targetImg = SegImage::where('id', $data->dataSrc)->with('mask')->first();
            //error_log(print_r($targetImg->toArray(),true));
            if(!$targetImg->mask){
                $mask = new SegMask;
            }else{
                $mask = $targetImg->mask;
            }
            $mask->source = $data->dataSrc;
            $mask->NbrSeg = $data->nbrSegments;
            $mask->compactness = $data->compactness;
            $mask->user = $currentUser->id;
            //$mask->slicStr = $data->slic->slic->data;
            error_log("avant slic");
            $mask->slicStr = $data->slicStr;
            error_log("avant tag");
            $mask->segInfo = $data->segInfo;
            error_log("apres tag");
            try{
                error_log("try save");
                $mask->save(); // returns false
            }
            catch(\Exception $e){
                // do task when error
                error_log("error save");
                $catchedError = $e->getMessage();   // insert query
                $log = substr($catchedError, 0, 1000);
                error_log(print_r($log,true));
                return $response->withJson([], 489, JSON_PRETTY_PRINT);
            }


            //$mask->save();
            error_log("masque apres save");
            
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

        if(!$currentUser){
            $currentUser = new \stdClass();
            $currentUser->id = -1;
        }

        $areaReceived = [];
        foreach ($data->areas as $area) {//for each area submitted
            if(!isset($area->id))$area->id = -1;
            array_push($areaReceived, $area->id);
            if($area->id > 0){
                $bddArea = ImgArea::where ('id', '=', $area->id)
                        ->first();
                if(($bddArea->user == $currentUser->id || $bddArea->user == $this->USER_ID_UNIVERSAL)&& $bddArea->state == $this->AREA_STATE_PENDING){
                    $bddArea->rectType = $area->rectType;
                    $bddArea->rectLeft  = $area->rectLeft;
                    $bddArea->rectTop  = $area->rectTop;
                    $bddArea->rectRight  = $area->rectRight;
                    $bddArea->rectBottom  = $area->rectBottom;
                    if($bddArea->user == $this->USER_ID_UNIVERSAL){
                        $bddArea->user = $currentUser->id;
                    }
                    $bddArea->save();
                    array_push($areaReceived, $bddArea->id);
                }
            }else if($area->id <= 0){
                //Create area
                $bddArea = ImgArea::firstOrCreate(
                            ['source' => $data->dataSrc,
                            'rectType' => $area->rectType,
                            'rectLeft' => round($area->rectLeft),
                            'rectTop' => round($area->rectTop),
                            'rectRight' => round($area->rectRight),
                            'rectBottom' => round($area->rectBottom)]);
                //$bddArea = new ImgArea;
                $bddArea->source = $data->dataSrc;
                $bddArea->rectType = $area->rectType;
                $bddArea->rectLeft  = $area->rectLeft;
                $bddArea->rectTop  = $area->rectTop;
                $bddArea->rectRight  = $area->rectRight;
                $bddArea->rectBottom  = $area->rectBottom;
                if($currentUser->id >= 0){
                    $bddArea->user = $currentUser->id;
                }else{
                    $unknownUser = $this->USER_ID_UNKNOWN;//-2 : unknowm user | -1 : non-log user
                    $bddArea->user = $unknownUser;
                }

                $bddArea->state = $this->AREA_STATE_PENDING;
                $bddArea->save();
                array_push($areaReceived, $bddArea->id);
            }
        }
        $prevBddAreas = ImgArea::where ('source', '=', $data->dataSrc)
                        ->where ('user', '=', $currentUser->id)
                        ->where('state', '=', $this->AREA_STATE_PENDING)
                        ->get();
        foreach ($prevBddAreas as $prevBddArea) {
            if(array_search($prevBddArea->id, $areaReceived) !== false){
                //update area in other loop
            }
            else{
                error_log("area NOT found ". $prevBddArea->id);
                $prevBddArea->forceDelete();
            }
        }
        error_log(print_r($data,true));
        if($data->validImage == 1){
            $img = imgLinks::where('id', $data->dataSrc)->first();
            $img->state = $this->IMG_STATE_PENDING;
            $img->save();
        }
        return;
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

        if($data->validateType == $this->IMG_VALIDATION_SAVEANDTOTAG) $state = $this->IMG_STATE_FORTAG;
        else if($data->validateType == $this->IMG_VALIDATION_SAVEANDVALID) $state = $this->IMG_STATE_VALIDATED;
        else if($data->validateType == $this->IMG_VALIDATION_TOTAG){
            $state = $this->IMG_STATE_FORTAG;
            $data->areas = [];
        }

        $areaReceived = [];
        foreach ($data->areas as $area) {//for each area submitted
            error_log(print_r($area,true));
            if(!isset($area->id))$area->id = -1;
            if(!isset($area->selected))$area->selected = 0;
            if($area->id > 0 && $area->selected == 1){//if id && selected update area
                //Save area validated
                $bddArea = ImgArea::where('id', '=', $area->id)->first();
                $bddArea->rectType = $area->rectType;
                $bddArea->rectLeft  = $area->rectLeft;
                $bddArea->rectTop  = $area->rectTop;
                $bddArea->rectRight  = $area->rectRight;
                $bddArea->rectBottom  = $area->rectBottom;
                if($state) $bddArea->state  = $this->AREA_STATE_VALIDATED;
                $bddArea->save();
                array_push($areaReceived, $bddArea->id);
            }elseif($area->id <= 0 && $area->selected == 1){//if !id && selected create area
                //Create area validated
                $bddArea = new ImgArea;
                $bddArea->source = $data->dataSrc;
                $bddArea->rectType = $area->rectType;
                $bddArea->rectLeft  = $area->rectLeft;
                $bddArea->rectTop  = $area->rectTop;
                $bddArea->rectRight  = $area->rectRight;
                $bddArea->rectBottom  = $area->rectBottom;
                $bddArea->user = $currentUser->id;
                if($state) $bddArea->state  = $this->AREA_STATE_VALIDATED;
                $bddArea->save();
                array_push($areaReceived, $bddArea->id);
            }elseif($area->id > 0 && !$area->selected){//if id && !selected delete area
                $bddArea = ImgArea::where('id', '=', $area->id)->first();
                $bddArea->state  = $this->AREA_STATE_REJECTED;
                $bddArea->save();
                $bddArea->delete();
            }else{//if !id && !selected ignore
                //ignore
            }
            
        }
        //delete area in dbb with no updated infos (ie removed from image)
        //2nd check (for) to delete the area that are not in new areas
        if($data->validateType != $this->IMG_VALIDATION_TOTAG){
            $prevBddAreas = ImgArea::where ('source', '=', $data->dataSrc)->get();
            error_log(print_r($areaReceived,true));
            foreach ($prevBddAreas as $prevBddArea) {
                if(array_search($prevBddArea->id, $areaReceived) !== false){
                    error_log("area found ".$prevBddArea->id);
                }
                else{
                    error_log("area NOT found ". $prevBddArea->id);
                    $prevBddArea->state = $this->AREA_STATE_REJECTED;
                    $prevBddArea->save();
                    $prevBddArea->delete();
                }
            }
        }
        $img = imgLinks::where('id', $data->dataSrc)->first();
        if($state) $img->state = $state;
        $img->save();
        
        return;
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

        if($data->validateType == 0){
            //$this->deleteSegAreas($data->dataSrc,FALSE);
        }

        $segimg = SegImage::where('id', $data->dataSrc)->with('mask')->first();
        //$Areas = SegArea::where('source', '=', $segimg->id)->get();
        $mask = $segimg->mask;

        if($data->validateType == 0){
            $state = 4;
        }
        else{
            $state = 3;
            //search mask
            //replace with new
            //$mask->user = $currentUser->id;
            $mask->segInfo = $data->segInfo;
            $mask->save();
        }
        $segimg->state = $state;
        //foreach ($Areas as $Area) {
        //    $Area->state = $state;
        //    $Area->save();
        //}
        $segimg->save();
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
            else{
                $area = SegArea::where('source', '=', $source)->first();
                if($area) {
                    $area->state = $this->AREA_STATE_REJECTED;
                    $area->save();
                }
                $rowsToDelete = SegArea::where('source', '=', $source)->delete();
            }
        }
    }


    /**
     * Request areas (stream)
     *
     * This page do not requires authentication.
     * Request type: GET
     */
    public function areaKeepUpdated($request, $response, $args){

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        $Areas = ImgArea::where('source', '=', $args['img_id'])
                        ->with('category')
                        ->get();
        $array = $Areas->toArray();

        $array2 = [];
        foreach ($array as $area) {
            if($area["user"] == $currentUser->id || $area["user"] == $this->USER_ID_UNIVERSAL)
                $area["owned"] = 1;
            else $area["owned"] = 0;
            array_push($array2, $area);
        }

        $data = json_encode($array2);
        
        return $response
            ->withHeader("Content-Type", "text/event-stream")
            ->withHeader("Cache-Control", "no-cache")
            ->write("retry: 3000\ndata: {$data}\n\n");
    }
}