<?php

namespace UserFrosting\Sprinkle\Site\Controller;

use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Support\Exception\ForbiddenException;
use Alchemy\Zippy\Zippy;
use Chumper\Zipper\Zipper;
use UserFrosting\Sprinkle\Site\Controller\UploadHandler;

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

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;
        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;
        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_label')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

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

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;
        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;
        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_upload')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        return $this->ci->view->render($response, 'pages/upload.html.twig');
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
        

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;
        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;
        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_validate')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        return $this->ci->view->render($response, 'pages/validate.html.twig');
    }
    /**
     * Prepare a Zip file to be download.
     *
     * Request type: POST
     */
    public function prepareZip($request, $response, $args)
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

        // Get parameters: 
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);

        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);

        if (!empty($data))
        {

            $category = mysqli_real_escape_string($db,($data->category));
            /////////////SELECT ////////////////
            $sql = "SELECT lnk.path
                    FROM labelimglinks lnk LEFT JOIN labelimgarea are ON lnk.id =are.source AND are.alive = 1
                    WHERE are.alive = 1 AND lnk.validated = 1 AND are.rectType = '$category'
                    GROUP BY lnk.id";

            $result = $db->query($sql);
            
            $imgFound = $result->num_rows;
            
            if ($imgFound > 0) {
            
                $sql = "SELECT cat.Category FROM labelimgcategories cat WHERE cat.id= '$category'";
                $cat = $db->query($sql);
                $catRes = $cat->fetch_object();
                $tmpFolder = sha1(rand().microtime());
                error_log("Folder : ".$tmpFolder);
                
                if(!$this->saveTmpFolder($tmpFolder,$tmpFolder."/".$catRes->Category.".zip",$db))
                    exit;
                
                mkdir("../tmp/".$tmpFolder, 0700);
                $filename = ("../tmp/".$tmpFolder."/".$catRes->Category.".zip");


                
                $zip = new \ZipArchive();
                $zip->open($filename, \ZipArchive::CREATE);
                
                $fileNameLink = "";
                /* fetch object array */
                while ($obj = $result->fetch_object()) {
                    $path_parts = pathinfo($obj->path);
                    $fileNameLink = $path_parts['filename'];
                    //error_log("Fill file ".$path_parts['filename'].".txt");
                    $txtfile = fopen("../tmp/".$tmpFolder."/".$path_parts['filename'].".txt", "w") or die("Unable to open file!");
                    $sql = "SELECT cat.Category,are.rectLeft,are.rectTop,are.rectRight,are.rectBottom
        FROM labelimglinks lnk LEFT JOIN labelimgarea are ON lnk.id =are.source AND are.alive = 1 LEFT JOIN labelimgcategories cat ON cat.id=are.rectType
        WHERE are.alive = 1 AND lnk.validated = 1 AND are.rectType = '$category' AND lnk.path = '$obj->path'"; 
                    $rows = $db->query($sql);
                    $curImg = 0;
                    while ($rect = $rows->fetch_object()) {
                        $line = $rect->Category." 0 0 0 ".$rect->rectLeft." ".$rect->rectTop." ".$rect->rectRight." ".$rect->rectBottom." 0 0 0 0 0 0 0";
                        fwrite($txtfile, $line);
                        fwrite($txtfile, "\n");
                        //error_log($line);
                        $curImg++;
                        $prct = $curImg/$imgFound*100;
                    }
                    fclose($txtfile);

                    $zip->addFile("../tmp/".$tmpFolder."/".$path_parts['filename'].".txt", $path_parts['filename'].".txt");
                    $zip->addFile("../img/".$obj->path, $obj->path);
                }
                
                /* free result set */
                $zip->close();
                $cat->close();
                $result->close();
                $rows->close();

                $files = glob('../tmp/'.$tmpFolder.'/*.{txt}', GLOB_BRACE);
                foreach($files as $file) {
                  unlink($file);
                }
                
                $res=array("link"=>$tmpFolder,"msg"=>"Download Ready");
                echo json_encode($res);
                
            }
            else
                echo json_encode("No file found");
            
        }
        else // $_POST is empty.
        {
            echo json_encode("No data");
        }


        //clean tmp folder
        $dir    = '../tmp';
        $tmpArray = scandir($dir);
        foreach($tmpArray as $file){
            $folder = basename($file);
            if(strlen($folder) == 40){
                $sql = "SELECT `token`,`expires` FROM `labelimgexportlinks` WHERE token = '$folder'";
                $tokens = $db->query($sql);
                while ($token = $tokens->fetch_object()) {
                    if(date('Y-m-d H:i:s') > $token->expires ){
                        $sql = "DELETE FROM `labelimgexportlinks` WHERE token = '$folder'"; 
                        if ($db->query($sql) === TRUE) {
                            if(file_exists ("../tmp/".$token->token))
                                $this->rrmdir("../tmp/".$token->token);
                        } else {
                            echo "Error: " . $sql . "<br>" . $db->error;
                        }
                        error_log("Clean : ".$folder);
                    }else{
                        error_log(date('Y-m-d H:i:s')." NO Clean : ".$token->expires." ".$folder);
                    }
                }
                $count = mysqli_num_rows($tokens);
                if($count == 0) 
                    if(file_exists ("../tmp/".$folder))
                        $this->rrmdir("../tmp/".$folder);
            }
        }
        $sql = "SELECT `token`,`expires` FROM `labelimgexportlinks` WHERE 1";
        $tokens = $db->query($sql);
        while ($token = $tokens->fetch_object()) {
            if(date('Y-m-d H:i:s') > $token->expires ){
                $sql = "DELETE FROM `labelimgexportlinks` WHERE token = '$token->token'";   
                if ($db->query($sql) === TRUE) {
                    error_log("Clean DB: ".$token->token);
                    if(file_exists ("../tmp/".$token->token))
                        $this->rrmdir("../tmp/".$token->token);
                } else {
                    echo "Error: " . $sql . "<br>" . $db->error;
                }
            }
        }
    }

    /**
     * Return file request to download
     *
     * Request type: GET
     */
    public function returnDownload($request, $response, $args)
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

        error_log("returnDownload args");
        error_log(print_r($args,true));

        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);

        error_log("enter dl");

        if(!isset($args['dl_id'])) {
            error_log("No token");
            exit;
        }
        else{
            error_log($args['dl_id']);
        }
        error_log("etape 1");
        $tmpPath = "../tmp/";
        $token = mysqli_real_escape_string($db,$args['dl_id']);
        $sql = "SELECT exlk.archivePath FROM labelimgexportlinks exlk WHERE exlk.token = '$token'";
        $res = $db->query($sql);
        $tmpLink = $res->fetch_object();

        $count = mysqli_num_rows($res);
        error_log($count);
        if($count != 1) 
            exit;
        error_log("etape 2");

        $filename = $tmpPath.$tmpLink->archivePath;

        if (!file_exists($filename)) 
            exit;
        error_log("etape 3");
        error_log($filename);
        // send $filename to browser
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filename);
        $size = filesize($filename);
        $name = basename($filename);
         error_log("etape 4");
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            // cache settings for IE6 on HTTPS
            header('Cache-Control: max-age=120');
            header('Pragma: public');
        } else {
            header('Cache-Control: private, max-age=120, must-revalidate');
            header("Pragma: no-cache");
        }
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // long ago
        header("Content-Type: $mimeType");
        header('Content-Disposition: attachment; filename="' . $name . '";');
        header("Accept-Ranges: bytes");
        header('Content-Length: ' . filesize($filename));
         
        print readfile($filename);
        error_log("etape 5");
        $this->rrmdir($tmpPath.$token);

        $sql = "DELETE FROM `labelimgexportlinks` WHERE `labelimgexportlinks`.`token` = '$token'";
        if(!$db->query($sql))
            error_log("Delete failed in labelimgexportlinks");
        exit;


    }
    /**
     * Renders a simple "upload" page for Users.
     *
     * Request type: GET
     */
    public function uploadHandler($request, $response, $args)
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

        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);

        //include('UploadHandler.php');
        error_reporting(E_ALL | E_STRICT);
        $upload_handler = new UploadHandler();

    }

    private function rrmdir($src) {
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $src . '/' . $file;
                if ( is_dir($full) ) {
                    $this->rrmdir($full);
                }
                else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }

    private function saveTmpFolder($token, $archivePath,$db){
        $aliveTime = time() + (6 * 60 * 60);
        $expires = date('Y-m-d H:i:s', $aliveTime);
        
        $sql = "
                INSERT INTO labelimgexportlinks (token, archivePath, expires)
                VALUES ('$token','$archivePath','$expires')";

        //check insert
        if ($db->query($sql) === TRUE) {
            return true;
        } else {
            return false;
        }
    }
}