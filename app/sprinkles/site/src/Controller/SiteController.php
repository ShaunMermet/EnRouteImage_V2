<?php

namespace UserFrosting\Sprinkle\Site\Controller;

use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Support\Exception\ForbiddenException;

/**
 * Controller class for site-related requests.
 *
 * @author 
 */
class SiteController extends SimpleController
{
	/**
     * Renders a simple "label" page for Users.
     *
     * Request type: GET
     */


    public function pageLabel($request, $response, $args)
    {

        /** @var UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
        $authenticator = $this->ci->authenticator;
        if (!$authenticator->check()) {
            $loginPage = $this->ci->router->pathFor('login');
            return $response->withRedirect($loginPage, 400);
        }

        #error_log("check rights")
        #/** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        #$authorizer = $this->ci->authorizer;
        #/** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        #$currentUser = $this->ci->currentUser;
        #// Access-controlled page
        #if (!$authorizer->checkAccess($currentUser, 'user_label')) {
        #    $loginPage = $this->ci->router->pathFor('login');
        #   return $response->withRedirect($loginPage, 400);
        #}


        return $this->ci->view->render($response, 'pages/label.html.twig');
    }
	
	/**
     * Renders a simple "upload" page for Users.
     *
     * Request type: GET
     */
    public function pageUpload($request, $response, $args)
    {
        /** @var UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
        $authenticator = $this->ci->authenticator;
        if (!$authenticator->check()) {
            $loginPage = $this->ci->router->pathFor('login');
            return $response->withRedirect($loginPage, 400);
        }
        return $this->ci->view->render($response, 'index.html.twig');
    }
	/**
     * Renders a simple "upload" page for Users.
     *
     * Request type: GET
     */
    public function pageValidate($request, $response, $args)
    {
        /** @var UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
        $authenticator = $this->ci->authenticator;
        if (!$authenticator->check()) {
            $loginPage = $this->ci->router->pathFor('login');
            return $response->withRedirect($loginPage, 400);
        }
        return $this->ci->view->render($response, 'pages/validate.html.twig');
    }
}