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
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Sprinkle\Site\Sprunje\ImgLinksSprunje;
use UserFrosting\Sprinkle\Site\Sprunje\SegImageSprunje;
use UserFrosting\Sprinkle\Site\Model\ImgLinks;
use UserFrosting\Sprinkle\Site\Model\SegImage;
use UserFrosting\Sprinkle\Site\Model\Set;
use UserFrosting\Sprinkle\Site\Model\SegSet;

/**
 * Controller class for category-related requests.
 *
 * @author 
 * @see 
 */
class ImageController extends SimpleController
{
    /**
     * Returns all images that are neither validated nor annotated.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getImagesC($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_label')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

        $validSet = [];
        foreach ($UserWGrp->group as $group) {
            $sets = Set::where('group_id', '=', $group->id)
                    ->get();
            foreach ($sets as $set) {
                array_push($validSet, $set->id);
            }
        }

        
        $maxImageRequested = 1;//getenv('MAX_IMAGE_REQUESTED');

        // GET parameters
        $params = $request->getQueryParams();
        $requestedSet = $params["setID"];
        if($requestedSet == null) $requestedSet = 1;
        
        $imgLinks = ImgLinks::where(function ($imgLinks){
                    $imgLinks->where ('state', '=', 1)
                            ->orWhere ('state', '=', 4);
                    })
                    //->where ('available', '=', 1)
                    ->whereIn('set_id', $validSet)
                    ->where ('set_id', '=', $requestedSet)
                    ->inRandomOrder()
                    ->limit($maxImageRequested)
                    ->get();
        //Reserve Images
        foreach ($imgLinks as $img) {
            $img->available = 0;
            $img->requested = date("Y-m-d H:i:s");
            $img->save();

            $this->createLightImgBbox($img->path);

            //$UserWGrp->requestedImgId = $img->id;
        }

        $result = $imgLinks->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);

    }
    /**
     * Returns all seg images that are neither validated nor annotated.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getSegImagesC($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_label')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

        $validSet = [];
        foreach ($UserWGrp->group as $group) {
            $sets = SegSet::where('group_id', '=', $group->id)
                    ->get();
            foreach ($sets as $set) {
                array_push($validSet, $set->id);
            }
        }

        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        // GET parameters
        $params = $request->getQueryParams();
        $requestedSet = $params["setID"];
        if($requestedSet == null) $requestedSet = 1;
        
        if($requestedSet == null) $requestedSet = 1;

        $segImg = SegImage::where(function ($imgLinks){
                $imgLinks->where ('state', '=', 1)
                        ->orWhere ('state', '=', 4);
                })
                ->whereIn('set_id', $validSet)
                ->where ('set_id', '=', $requestedSet)
                ->inRandomOrder()
                ->limit($maxImageRequested)
                ->get();

        foreach ($segImg as $img) {
            $this->createLightImgSeg($img->path);
        }

        $result = $segImg->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);

    }

    /**
     * Returns all images that are neither validated nor annotated.
     *
     * 
     * Request type: GET
     */
    public function getImagesCNoAuth($request, $response, $args)
    {
        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $validSet = [];
        $sets = Set::where('group_id', '=', 1)
                ->get();
        foreach ($sets as $set) {
            array_push($validSet, $set->id);
        }

        // GET parameters
        $params = $request->getQueryParams();
        $requestedSet = $params["setID"];
        if($requestedSet == null) $requestedSet = 1;

        $imgLinks = ImgLinks::where(function ($imgLinks){
                    $imgLinks->where ('state', '=', 1)
                            ->orWhere ('state', '=', 4);
                    })
                    //->where ('available', '=', 1)
                    ->whereIn('set_id', $validSet)
                    ->where ('set_id', '=', $requestedSet)
                    ->inRandomOrder()
                    ->limit($maxImageRequested)
                    ->get();
        //Reserve Images
        foreach ($imgLinks as $img) {
            $img->available = 0;
            $img->requested = date("Y-m-d H:i:s");
            $img->save();

            $this->createLightImgBbox($img->path);
        }

        $result = $imgLinks->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Returns all images that have been annotated, waiting for validation.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getImagesA($request, $response, $args)
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

        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

        $validSet = [];
        foreach ($UserWGrp->group as $group) {
            $sets = Set::where('group_id', '=', $group->id)
                    ->get();
            foreach ($sets as $set) {
                array_push($validSet, $set->id);
            }
        }

        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        // GET parameters
        $params = $request->getQueryParams();
        $requestedSet = $params["setID"];
        if($requestedSet == null) $requestedSet = 1;

        $imgLinks = ImgLinks::where ('state', '=', 2)
                    //->where ('available', '=', 1)
                    ->whereIn('set_id', $validSet)
                    ->where ('set_id', '=', $requestedSet)
                    ->inRandomOrder()
                    ->limit($maxImageRequested)
                    ->get();
        //Reserve Images
        foreach ($imgLinks as $img) {
            $img->available = 0;
            $img->requested = date("Y-m-d H:i:s");
            $img->save();

            $this->createLightImgBbox($img->path);
        }

        $result = $imgLinks->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Returns all seg images that have been annotated, waiting for validation.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getSegImagesA($request, $response, $args)
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

        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

       $validSet = [];
        foreach ($UserWGrp->group as $group) {
            $sets = SegSet::where('group_id', '=', $group->id)
                    ->get();
            foreach ($sets as $set) {
                array_push($validSet, $set->id);
            }
        }

        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        // GET parameters
        $params = $request->getQueryParams();
        $requestedSet = $params["setID"];
        if($requestedSet == null) $requestedSet = 1;

        $segImg = SegImage::where ('state', '=', 2)
                    ->whereIn('set_id', $validSet)
                    ->where ('set_id', '=', $requestedSet)
                    ->inRandomOrder()
                    ->limit($maxImageRequested)
                    ->get();
        
        foreach ($segImg as $img) {
            $this->createLightImgSeg($img->path);
        }

        $result = $segImg->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }


    /**
     * Say that image is no longer in use and so can be requested again.
     *
     * This page requires authentication.
     * Request type: PUT
     */
    public function freeImage($request, $response, $args)
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

        // Get PUT parameters: 
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);
        
        $img = ImgLinks::where('id', $data->dataSrc)->first();
        $img->available = 1;
        $img->save();
    }

    /**
     * Say that image is no longer in use and so can be requested again.
     *
     * 
     * Request type: PUT
     */
    public function freeImageNoAuth($request, $response, $args)
    {

        // Get PUT parameters: 
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);
        
        $img = ImgLinks::where('id', $data->dataSrc)->first();
        $img->available = 1;
        $img->save();

    }
    /**
     * Get the number of images corresponding to one set (set based / validated).
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getNbrImagesBySet($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_export')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

        $validSet = [];
        foreach ($UserWGrp->group as $group) {
            $sets = Set::where('group_id', '=', $group->id)
                    ->get();
            foreach ($sets as $set) {
                array_push($validSet, $set->id);
            }
        }

        // GET parameters
        $params = $request->getQueryParams();
        $requestedSet = $params["setID"];
        if($requestedSet == null) $requestedSet = 1;
        
        $count['countBySet'] = ImgLinks::where ('state', '=', 3)
                                ->whereIn('set_id', $validSet)
                                ->where ('set_id', '=', $requestedSet)
                                ->count();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($count, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Get the number of segimages corresponding to one category (area based).
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getNbrSegImagesBySet($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_export')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

        $validSet = [];
        foreach ($UserWGrp->group as $group) {
            $sets = SegSet::where('group_id', '=', $group->id)
                    ->get();
            foreach ($sets as $set) {
                array_push($validSet, $set->id);
            }
        }


        // GET parameters
        $params = $request->getQueryParams();
        $requestedSet = $params["setID"];
        if($requestedSet == null) $requestedSet = 1;
        
        $count['countBySet'] = SegImage::where ('state', '=', 3)
                                ->whereIn('set_id', $validSet)
                                ->where ('set_id', '=', $requestedSet)
                                ->count();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($count, 200, JSON_PRETTY_PRINT);
    }
    /**
     * Get the images corresponding to sprunje filter.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getImageSprunje($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_validated')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

        $validSet = [];
        foreach ($UserWGrp->group as $group) {
            $sets = Set::where('group_id', '=', $group->id)
                    ->get();
            foreach ($sets as $set) {
                array_push($validSet, $set->id);
            }
        }
        //$params['filters']['set_id'] = implode("||",$validSet);

        $sprunje = new ImgLinksSprunje($classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }
    /**
     * Get the segimages corresponding to sprunje filter.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getSegImageSprunje($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_validated')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

        $validSet = [];
        foreach ($UserWGrp->group as $group) {
             $sets = SegSet::where('group_id', '=', $group->id)
                    ->get();
            foreach ($sets as $set) {
                array_push($validSet, $set->id);
            }
        }
        
        $sprunje = new SegImageSprunje($classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Edit an image.
     *
     * This function requires authentication.
     * Request type: PUT
     */
    public function editImage($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_upload')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        // Get PUT parameters: (name, slug, icon, description)
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);

        if(!array_key_exists('imgId',$data)){
            error_log ("no image id");
            echo "FAIL";
            exit;
        }
        $imgId = $data->imgId;
        if($data->imgSet){
            $imgSet = $data->imgSet;
        }
        
        
        if ($data->imgId == ""){
            error_log ("no image");
            echo "FAIL";
            exit;
        }
        
        //TODO Insert
        $img = ImgLinks::where ('id', '=', $imgId)
                    ->first();
        if($imgSet){
            //error_log("change set id");
            $img->set_id = $imgSet;
        }
        $img->save();
    }
    /**
     * Edit a segimage.
     *
     * This function requires authentication.
     * Request type: PUT
     */
    public function editSegImage($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_upload')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        // Get PUT parameters: (name, slug, icon, description)
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);

        if(!array_key_exists('imgId',$data)){
            error_log ("no image id");
            echo "FAIL";
            exit;
        }
        $imgId = $data->imgId;
        if($data->imgSet){
            $imgSet = $data->imgSet;
        }
        
        
        if ($data->imgId == ""){
            error_log ("no image");
            echo "FAIL";
            exit;
        }
        
        //TODO Insert
        $img = SegImage::where ('id', '=', $imgId)
                    ->first();
        if($imgSet){
            //error_log("change set id");
            $img->set_id = $imgSet;
        }
        $img->save();
    }

    protected function createLightImgBbox($imgName){
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $img = ImgLinks::where ('path', '=', $imgName)
                    ->with('group')
                    ->first();
        if(is_null($img)) {
            error_log($imgName." img not found");
            return;
        }
        $table = $img->toArray();
        $imgRate = $table["cprs_rate"];
        $groupRate = $table["group"]["bb_cprs_rate"];
        if(is_null($groupRate)){
            $publicGrp = $classMapper->staticMethod('group', 'where', 'id', 1)->first();
            $groupRate = $publicGrp->bb_cprs_rate;
        }
            
        $source = "img/".$imgName;
        $dest = "img/light/".$imgName;
        if($imgRate != $groupRate){
            $this->createLightImg($source,$dest,$imgName,$groupRate);
            $img->cprs_rate = $groupRate;
            $img->save();
        }
        if(!file_exists($dest)){
            $this->createLightImg($source,$dest,$imgName,$groupRate);
            $img->cprs_rate = $groupRate;
            $img->save();
        }

    }
    protected function createLightImgSeg($imgName){
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $img = SegImage::where ('path', '=', $imgName)
                    ->with('group')
                    ->first();
        if(is_null($img)) {
            error_log($imgName." img not found");
            return;
        }
        $table = $img->toArray();
        $imgRate = $table["cprs_rate"];
        $groupRate = $table["group"]["bb_cprs_rate"];
        if(is_null($groupRate)){
            $publicGrp = $classMapper->staticMethod('group', 'where', 'id', 1)->first();
            $groupRate = $publicGrp->bb_cprs_rate;
        }
            
        $source = "img/".$imgName;
        $dest = "img/segmentation/light/".$imgName;
        if($imgRate != $groupRate){
            $this->createLightImg($source,$dest,$imgName,$groupRate);
            $img->cprs_rate = $groupRate;
            $img->save();
        }
        if(!file_exists($dest)){
            $this->createLightImg($source,$dest,$imgName,$groupRate);
            $img->cprs_rate = $groupRate;
            $img->save();
        }
    }
    protected function createLightImg($imgPath,$destPath,$imgName,$scale){
        $before = ini_get('memory_limit');
        ini_set('memory_limit', '512M');

        $originalImg = $this->imageCreateFromAny($imgPath);
        imagejpeg($originalImg,$destPath,$scale);
        
        ini_set('memory_limit', $before);
        
    }
    protected function imageCreateFromAny($filepath) { 
        $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize() 
        $allowedTypes = array( 
            1,  // [] gif 
            2,  // [] jpg 
            3,  // [] png 
            6   // [] bmp 
        ); 
        if (!in_array($type, $allowedTypes)) { 
            return false; 
        } 
        switch ($type) { 
            case 1 : 
                $im = imageCreateFromGif($filepath); 
            break; 
            case 2 : 
                $im = imageCreateFromJpeg($filepath); 
            break; 
            case 3 : 
                $im = imageCreateFromPng($filepath); 
            break; 
            case 6 : 
                $im = imageCreateFromBmp($filepath); 
            break; 
        }    
        return $im;  
    }
    

}
