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

        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);
        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');
        // Set autocommit to off
        mysqli_autocommit($db,FALSE);

        /////////////SELECT ////////////////

        $sql = "SELECT lnk.id,lnk.path
        FROM labelimglinks lnk LEFT JOIN labelimgarea are ON lnk.id =are.source AND are.alive = 1
        WHERE (are.alive = 0 OR are.alive IS NULL) AND lnk.available = 1
        GROUP BY lnk.id
        ORDER BY RAND()
        LIMIT $maxImageRequested";
        $result = $db->query($sql);
        header('Content-type: application/json');
        $res=array();
        // Commit transaction
        mysqli_commit($db);
        if ($result->num_rows > 0) {
            
            
            /* fetch object array */
            while ($obj = $result->fetch_object()) {
                $sql = "UPDATE `labelimglinks` SET `available` = 0,`requested`= NOW() WHERE `labelimglinks`.`id` = '$obj->id'";
                    
                if ($db->query($sql) === TRUE) {
                } else {
                    echo "Error: " . $sql . "<br>" . $db->error;
                }
                
                array_push($res,$obj);
                error_log("label : found row");
            }
            error_log("label : ".count($res));
            echo json_encode($res);
            
            
            // Commit transaction
            mysqli_commit($db);

            /* free result set */
            $result->close();
            
        } else {
            
        }

        foreach($res as $img){
            $sql = "UPDATE `labelimglinks` SET `available` = 0 WHERE `labelimglinks`.`id` = '$img->id'";
            if ($db->query($sql) === TRUE) {
                error_log("label : set 0 ".$img->id);
            } else {
                echo "Error: " . $sql . "<br>" . $db->error;
            }
        }
        ///////////////

        $db->close();
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
        
        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);
        $maxImageRequested = getenv('MAX_IMAGE_REQUESTED');

        // Set autocommit to off
        mysqli_autocommit($db,FALSE);
        /////////////SELECT ////////////////

        $sql = "SELECT lnk.id,lnk.path
        FROM labelimglinks lnk LEFT JOIN labelimgarea are ON lnk.id =are.source AND are.alive = 1
        WHERE are.alive = 1 AND lnk.validated = 0 AND lnk.available = 1
        GROUP BY lnk.id
        ORDER BY RAND()
        LIMIT $maxImageRequested";
        $result = $db->query($sql);
        header('Content-type: application/json');
        if ($result->num_rows > 0) {
            
            $res=array();
            /* fetch object array */
            while ($obj = $result->fetch_object()) {
                /*if ($db->query($sql) === TRUE) {
                } else {
                    echo "Error: " . $sql . "<br>" . $db->error;
                }*/
                array_push($res,$obj);
                error_log("validate : found row");
            }
            error_log("validate : ".count($res));
            echo json_encode($res);
            // Commit transaction
            mysqli_commit($db);
            
            foreach($res as $img){
                $sql2 = "UPDATE `labelimglinks` SET `available` = 0,`requested`= NOW() WHERE `labelimglinks`.`id` = '$img->id'";
                if ($db->query($sql2) === TRUE) {
                    error_log("validate : set 0 to ".$img->id);
                } else {
                    echo "Error: " . $sql2 . "<br>" . $db->error;
                }
            }
            // Commit transaction
            mysqli_commit($db);

            /* free result set */
            $result->close();
            
        } else {
            
        }
        ///////////////

        $db->close();
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

        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);

        if (!empty($data))
        {
            error_log("in freeImage\n") ;
            

            $source = mysqli_real_escape_string($db,($data->dataSrc));
            $sql = "UPDATE `labelimglinks` SET `available` = 1 WHERE `labelimglinks`.`id` = '$source'"; 
            if ($db->query($sql) === TRUE) {
                error_log("img ".$source." set to 1");
            } else {
                echo "Error: " . $sql . "<br>" . $db->error;
            }
            
            $db->close();
            
        }
        else // $_POST is empty.
        {
            error_log("freeImage - No data") ;
        }

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

        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);
        
        if (!empty($data))
        {
            $category = mysqli_real_escape_string($db,($data->category));
            
            /////////////SELECT ////////////////
            $sql = "SELECT lnk.id,are.rectType
                    FROM labelimglinks lnk LEFT JOIN labelimgarea are ON lnk.id =are.source AND are.alive = 1
                    WHERE are.alive = 1 AND lnk.validated = 1 AND are.rectType = '$category'
                    GROUP BY lnk.id";

            $result = $db->query($sql);
            header('Content-type: application/json');
            $res=array();
            array_push($res,$result->num_rows);
            echo json_encode($res);
            $result->close();
            ///////////////
            $db->close();
            
        }
        else // $_POST is empty.
        {
            echo "No data";
        }
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
