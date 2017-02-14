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

        include('config.php');

        // Set autocommit to off
        mysqli_autocommit($db,FALSE);

        /////////////SELECT ////////////////

        $sql = "SELECT lnk.id,lnk.path
        FROM labelimglinks lnk LEFT JOIN labelimgarea are ON lnk.id =are.source
        WHERE are.source IS NULL AND lnk.available = 1
        GROUP BY lnk.id
        ORDER BY RAND()
        LIMIT 20";
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
        include('config.php');

        // Set autocommit to off
        mysqli_autocommit($db,FALSE);
        /////////////SELECT ////////////////

        $sql = "SELECT lnk.id,lnk.path
        FROM labelimglinks lnk LEFT JOIN labelimgarea are ON lnk.id =are.source
        WHERE are.source IS NOT NULL AND lnk.validated = 0 AND lnk.available = 1
        GROUP BY lnk.id
        ORDER BY RAND()
        LIMIT 20";
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

        // Get PUT parameters: (name, slug, icon, description)
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);
        //error_log( print_r($data, TRUE) );

        include('config.php');
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
}
