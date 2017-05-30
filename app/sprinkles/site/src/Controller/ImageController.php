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

        $validGroup = ['NULL'];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }
        
        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        $imgLinks = ImgLinks::where(function ($imgLinks){
                    $imgLinks->where ('state', '=', 1)
                            ->orWhere ('state', '=', 4);
                    })
                    ->where ('available', '=', 1)
                    ->where(function ($imgLinks) use ($validGroup){
                    $imgLinks->whereIn('group', $validGroup)
                            ->orWhereNull('group');
                    })
                    ->orderBy('group', 'desc')
                    ->inRandomOrder()
                    ->limit($maxImageRequested)
                    ->with('group')
                    ->get();
        //Reserve Images
        foreach ($imgLinks as $img) {
            $img->available = 0;
            $img->requested = date("Y-m-d H:i:s");
            $img->save();

            $table = $img->toArray();
            $imgRate = $table["cprs_rate"];
            $groupRate = $table["group"]["bb_cprs_rate"];
            if(is_null($groupRate)){
                $publicGrp = $classMapper->staticMethod('group', 'where', 'id', 1)->first();
                $groupRate = $publicGrp->bb_cprs_rate;
            }
            if($imgRate != $groupRate){
                $this->createLightImgBbox($img->path,$groupRate);
                $img->cprs_rate = $groupRate;
                $img->save();
            }
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

        $validGroup = ['NULL'];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }

        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        $segImg = SegImage::where(function ($imgLinks){
                $imgLinks->where ('state', '=', 1)
                        ->orWhere ('state', '=', 4);
                })
                ->where(function ($imgLinks) use ($validGroup){
                $imgLinks->whereIn('group', $validGroup)
                        ->orWhereNull('group');
                })
                ->orderBy('group', 'desc')
                ->inRandomOrder()
                ->limit($maxImageRequested)
                ->with('group')
                ->get();

        $table = $segImg->toArray();
        $imgRate = $table["cprs_rate"];
        $groupRate = $table["group"]["bb_cprs_rate"];
        if(is_null($groupRate)){
            $publicGrp = $classMapper->staticMethod('group', 'where', 'id', 1)->first();
            $groupRate = $publicGrp->bb_cprs_rate;
        }
        if($imgRate != $groupRate){
            $this->createLightImgBbox($segImg->path,$groupRate);
            $segImg->cprs_rate = $groupRate;
            $segImg->save();
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
        $validGroup = [NULL,1];

        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $imgLinks = ImgLinks::where(function ($imgLinks){
                    $imgLinks->where ('state', '=', 1)
                            ->orWhere ('state', '=', 4);
                    })
                    ->where ('available', '=', 1)
                    ->where(function ($imgLinks) use ($validGroup){
                    $imgLinks->whereIn('group', $validGroup)
                            ->orWhereNull('group');
                    })
                    ->orderBy('group', 'desc')
                    ->inRandomOrder()
                    ->limit($maxImageRequested)
                    ->with('group')
                    ->get();
        //Reserve Images
        foreach ($imgLinks as $img) {
            $img->available = 0;
            $img->requested = date("Y-m-d H:i:s");
            $img->save();

            $table = $img->toArray();
            $imgRate = $table["cprs_rate"];
            $groupRate = $table["group"]["bb_cprs_rate"];
            if(is_null($groupRate)){
                $publicGrp = $classMapper->staticMethod('group', 'where', 'id', 1)->first();
                $groupRate = $publicGrp->bb_cprs_rate;
            }
            if($imgRate != $groupRate){
                $this->createLightImgBbox($img->path,$groupRate);
                $img->cprs_rate = $groupRate;
                $img->save();
            }
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

        $validGroup = ['NULL'];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }

        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        $imgLinks = ImgLinks::where ('available', '=', 1)
                    ->where ('state', '=', 2)
                    ->where(function ($imgLinks) use ($validGroup){
                    $imgLinks->whereIn('group', $validGroup)
                            ->orWhereNull('group');
                    })
                    ->orderBy('group', 'desc')
                    ->inRandomOrder()
                    ->limit($maxImageRequested)
                    ->with('group')
                    ->get();
        //Reserve Images
        foreach ($imgLinks as $img) {
            $img->available = 0;
            $img->requested = date("Y-m-d H:i:s");
            $img->save();

            $table = $img->toArray();
            $imgRate = $table["cprs_rate"];
            $groupRate = $table["group"]["bb_cprs_rate"];
            if(is_null($groupRate)){
                $publicGrp = $classMapper->staticMethod('group', 'where', 'id', 1)->first();
                $groupRate = $publicGrp->bb_cprs_rate;
            }
            if($imgRate != $groupRate){
                $this->createLightImgBbox($img->path,$groupRate);
                $img->cprs_rate = $groupRate;
                $img->save();
            }
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

        $validGroup = ['NULL'];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }

        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        $segImg = SegImage::where ('state', '=', 2)
                    ->where(function ($segImg) use ($validGroup){
                    $segImg->whereIn('group', $validGroup)
                            ->orWhereNull('group');
                    })
                    ->orderBy('group', 'desc')
                    ->inRandomOrder()
                    ->limit($maxImageRequested)
                    ->with('group')
                    ->get();
        
        $table = $segImg->toArray();
        $imgRate = $table["cprs_rate"];
        $groupRate = $table["group"]["bb_cprs_rate"];
        if(is_null($groupRate)){
            $publicGrp = $classMapper->staticMethod('group', 'where', 'id', 1)->first();
            $groupRate = $publicGrp->bb_cprs_rate;
        }
        if($imgRate != $groupRate){
            $this->createLightImgBbox($segImg->path,$groupRate);
            $segImg->cprs_rate = $groupRate;
            $segImg->save();
        }

        $result = $segImg->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Returns all images that have been annotated by current user, waiting for validation.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getImagesAbyMe($request, $response, $args)
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

        $validGroup = ['NULL'];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }

        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        $imgLinks = ImgLinks::whereHas('areas', function ($query) use ($currentUser){
                                $query->where('user', '=', $currentUser->id);
                            })
                    ->where ('available', '=', 1)
                    ->where ('state', '=', 2)
                    ->where(function ($imgLinks) use ($validGroup){
                    $imgLinks->whereIn('group', $validGroup)
                            ->orWhereNull('group');
                    })
                    ->orderBy('group', 'desc')
                    //->limit($maxImageRequested)
                    ->with('group')
                    ->get();
        //Reserve Images
        foreach ($imgLinks as $img) {
            $img->available = 0;
            $img->requested = date("Y-m-d H:i:s");
            $img->save();

            $table = $img->toArray();
            $imgRate = $table["cprs_rate"];
            $groupRate = $table["group"]["bb_cprs_rate"];
            if(is_null($groupRate)){
                $publicGrp = $classMapper->staticMethod('group', 'where', 'id', 1)->first();
                $groupRate = $publicGrp->bb_cprs_rate;
            }
            if($imgRate != $groupRate){
                $this->createLightImgBbox($img->path,$groupRate);
                $img->cprs_rate = $groupRate;
                $img->save();
            }
        }

        $result = $imgLinks->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Returns all seg images that have been annotated by current user, waiting for validation.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getSegImagesAbyMe($request, $response, $args)
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

        $validGroup = ['NULL'];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }

        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        $imgLinks = SegImage::whereHas('areas', function ($query) use ($currentUser){
                                $query->where('user', '=', $currentUser->id);
                            })
                    ->where ('state', '=', 2)
                    ->where(function ($imgLinks) use ($validGroup){
                    $imgLinks->whereIn('group', $validGroup)
                            ->orWhereNull('group');
                    })
                    ->orderBy('group', 'desc')
                    //->limit($maxImageRequested)
                    ->with('group')
                    ->get();

        $table = $segImg->toArray();
        $imgRate = $table["cprs_rate"];
        $groupRate = $table["group"]["bb_cprs_rate"];
        if(is_null($groupRate)){
            $publicGrp = $classMapper->staticMethod('group', 'where', 'id', 1)->first();
            $groupRate = $publicGrp->bb_cprs_rate;
        }
        if($imgRate != $groupRate){
            $this->createLightImgBbox($segImg->path,$groupRate);
            $segImg->cprs_rate = $groupRate;
            $segImg->save();
        }

        $result = $imgLinks->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }



    /**
     * Returns nbr images that have been annotated by current user, waiting for validation.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getCountImagesAbyMe($request, $response, $args)
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

        $validGroup = ['NULL'];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }

        $count = [];

        $count['pendingImg'] = ImgLinks::whereHas('areas', function ($query) use ($currentUser){
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
     * Returns nbr images that have been annotated by current user, waiting for validation.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getCountSegImagesAbyMe($request, $response, $args)
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

        $validGroup = ['NULL'];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }

        $count = [];

        $count['pendingSegImg'] = SegImage::whereHas('areas', function ($query) use ($currentUser){
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
     * Returns all images that have been validated.
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getImagesV($request, $response, $args)
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
     * Get the number of images corresponding to one category (area based).
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getNbrImagesByCat($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_export')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        // GET parameters
        $params = $request->getQueryParams();
        if (!array_key_exists("ids",$params)) $params["ids"] = [];
        if (!array_key_exists("groups",$params)) $params["groups"] = [1];
        
        $count['countByCat'] = ImgLinks::whereHas('areas', function ($query) use($params) {
                                    $query->whereIn('rectType', $params["ids"]);
                                })
                                ->where ('state', '=', 3)
                                ->where(function ($imgLinks) use ($params){
                                    if(in_array(1, $params["groups"])){
                                    $imgLinks->whereIn('group', $params["groups"])
                                            ->orWhereNull('group');
                                    }
                                    else{
                                        $imgLinks->whereIn('group', $params["groups"]);
                                    }
                                })
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
    public function getNbrSegImagesByCat($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_export')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        
        // GET parameters
        $params = $request->getQueryParams();
        if (!array_key_exists("ids",$params)) $params["ids"] = [];
        if (!array_key_exists("groups",$params)) $params["groups"] = [1];
        
        $count['countByCat'] = SegImage::whereHas('areas', function ($query) use($params) {
                                    $query->whereIn('areaType', $params["ids"]);
                                })
                                ->where ('state', '=', 3)
                                ->where(function ($imgLinks) use ($params){
                                    if(in_array(1, $params["groups"])){
                                    $imgLinks->whereIn('group', $params["groups"])
                                            ->orWhereNull('group');
                                    }
                                    else{
                                        $imgLinks->whereIn('group', $params["groups"]);
                                    }
                                })
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

        $validGroup = [];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }
        $params['filters']['group'] = implode("||",$validGroup);

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

        $validGroup = [];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }
        $params['filters']['group'] = implode("||",$validGroup);

        $sprunje = new SegImageSprunje($classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }
    protected function createLightImgBbox($imgName,$scale){
        $source = "img/".$imgName;
        $dest = "img/light/".$imgName;
        copy($source,$dest);
        $this->createLightImg($dest,$dest,$imgName,($scale));
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
