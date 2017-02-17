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

/**
 * Controller class for category-related requests.
 *
 * @author 
 * @see 
 */
class AreaController extends SimpleController
{
    /**
     * Returns all (alive) areas of all images.
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

        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);

        /////////////SELECT ////////////////

        $sql = "SELECT are.source,are.rectType,cat.Category,cat.Color,are.rectLeft,are.rectTop,are.rectRight,are.rectBottom
        FROM labelimglinks lnk 
        LEFT JOIN labelimgarea are ON lnk.id =are.source AND are.alive = 1
        LEFT JOIN labelimgcategories cat ON are.rectType = cat.id
        WHERE are.alive = 1 AND lnk.validated = 0";
        $result = $db->query($sql);
        header('Content-type: application/json');
        if ($result->num_rows > 0) {
            
            $res=array();
            /* fetch object array */
            while ($obj = $result->fetch_object()) {
                array_push($res,$obj);
            }
            echo json_encode($res);

            /* free result set */
            $result->close();
            
        } else {
            
        }
        ///////////////

        $db->close();
    }

    /**
     * Returns all areas of all images.
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
     * Returns all areas of all images.
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
            $sql = "UPDATE `labelimglinks` SET `validated` = $validated WHERE `labelimglinks`.`id` = '$source'";
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
            error_log("in deleteArea ".$source);
            //$sql = "DELETE FROM `labelimgarea` WHERE `source`= '$source'";
            $sql = "UPDATE `labelimgarea` SET `alive` = 0 WHERE `source`= '$source'";
            if ($db->query($sql) === TRUE) {
                error_log("delete done") ;
            } else {
                error_log("Error: " . $sql . "<br>" . $db->error) ;
            }
        }
    }
}
