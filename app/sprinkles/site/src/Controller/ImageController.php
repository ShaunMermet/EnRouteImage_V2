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
use UserFrosting\Sprinkle\Site\Model\ImgLinks;

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
        error_log("In getImagesN");
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


        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        $imgLinks = ImgLinks::whereDoesntHave('areas')
                    ->where ('available', '=', 1)
                    ->inRandomOrder()
                    ->limit($maxImageRequested)
                    ->get();
        //Reserve Images
        foreach ($imgLinks as $img) {
            $img->available = 0;
            $img->requested = date("Y-m-d H:i:s");
            $img->save();
        }

        $result = $imgLinks->toArray();

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

        $imgLinks = ImgLinks::whereDoesntHave('areas')
                    ->where ('available', '=', 1)
                    ->inRandomOrder()
                    ->limit($maxImageRequested)
                    ->get();
        //Reserve Images
        foreach ($imgLinks as $img) {
            $img->available = 0;
            $img->requested = date("Y-m-d H:i:s");
            $img->save();
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

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_validate')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }



        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        $imgLinks = ImgLinks::has('areas')
                    ->where ('available', '=', 1)
                    ->where ('validated', '=', 0)
                    ->inRandomOrder()
                    ->limit($maxImageRequested)
                    ->get();
        //Reserve Images
        foreach ($imgLinks as $img) {
            $img->available = 0;
            $img->requested = date("Y-m-d H:i:s");
            $img->save();
        }

        $result = $imgLinks->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
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
        //error_log( print_r($data, TRUE) );

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
        //error_log( print_r($data, TRUE) );

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

        // Get PUT parameters: 
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);
        //$category = $data->category;
        error_log($data->category);

        $count['countByCat'] = ImgLinks::whereHas('areas', function ($query) use($data) {
                                    $query->where('rectType', '=', $data->category);
                                })
                                ->where ('validated', '=', 1)
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

        $sprunje = new ImgLinksSprunje($classMapper, $params);

        //error_log("getImagesBySrcCat params ");
        //error_log(print_r($params, True));
        //error_log("getImagesBySrcCat Sprunje ");
        //error_log(print_r($sprunje, True));
        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }
    
}
