<?php

namespace UserFrosting\Sprinkle\Site\Controller;

use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Support\Exception\ForbiddenException;
use Alchemy\Zippy\Zippy;
use Chumper\Zipper\Zipper;
use UserFrosting\Sprinkle\Site\Controller\UploadHandler;
use UserFrosting\Sprinkle\Site\Model\SegImage;
use UserFrosting\Sprinkle\Site\Model\SegArea;
use UserFrosting\Sprinkle\Site\Model\ImgArea;
use UserFrosting\Sprinkle\Site\Model\ImgLinks;
use UserFrosting\Sprinkle\Site\Model\Set;
use UserFrosting\Sprinkle\Site\Model\SegSet;
use UserFrosting\Sprinkle\Site\Model\Token;

/**
 * Controller class for site-related requests.
 *
 * @author 
 */
class SiteController extends SimpleController
{
    /**
     * Renders a simple "index" page for Users.
     *
     * Request type: GET
     */


    public function pageIndex($request, $response, $args)
    {

        $config = $this->ci->config;
        $config['site.locales.selector'] = $args['locale'];

        $translator = $this->ci->translator;

        return $this->ci->view->render($response, 'pages/index.html.twig');
    }

    /**
     * Renders a simple "label" page for Users.
     *
     * Request type: GET
     */


    public function pageLabel($request, $response, $args)
    {

        $config = $this->ci->config;
        $config['site.locales.selector'] = $args['locale'];

        $translator = $this->ci->translator;

        return $this->ci->view->render($response, 'pages/label.html.twig');
    }

    /**
     * Renders the segmentation "label" page for Users.
     *
     * Request type: GET
     */


    public function pageSegLabel($request, $response, $args)
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

        return $this->ci->view->render($response, 'pages/seg-label.html.twig');
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
     * Renders a simple segmentation "upload" page for Users.
     *
     * Request type: GET
     */
    public function pageSegUpload($request, $response, $args)
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

        return $this->ci->view->render($response, 'pages/seg-upload.html.twig');
    }
	/**
     * Renders a simple "validate" page for Users.
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
     * Renders a simple "segValidate" page for Users.
     *
     * Request type: GET
     */
    public function pageSegValidate($request, $response, $args)
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

        return $this->ci->view->render($response, 'pages/seg-validate.html.twig');
    }
    /**
     * Renders a simple "validated" page for Users.
     *
     * Request type: GET
     */
    public function pageValidated($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_validated')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        return $this->ci->view->render($response, 'pages/validated.html.twig');
    }

    /**
     * Renders a simple "validated" page for Users.
     *
     * Request type: GET
     */
    public function pageTutorial($request, $response, $args)
    {
        $config = $this->ci->config;
        $config['site.locales.selector'] = $args['locale'];

        $translator = $this->ci->translator;

        return $this->ci->view->render($response, 'pages/tutorial.html.twig');
    }

    /**
     * Prepare an image Zip file to be download.
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

        $tmpFolderPath = "efs/tmp/";

        // Get parameters: 
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);
        error_log(print_r($data,true));
        
        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);

        if(!array_key_exists ('category',$data)) $data->category = [];
        if(!array_key_exists ('groups',$data)) $data->groups = [1];
        $requestedSet = $data->setID;
        if($requestedSet == null) $requestedSet = 1;
        error_log(print_r($data,true));
        if (!empty($data))
        {
            $imgToExport = ImgLinks::where ('state', '=', 3)
                                ->whereIn('set_id', $validSet)
                                ->where ('set_id', '=', $requestedSet)
                                ->get();
            
            if (count($imgToExport) > 0) {
            
                $tmpFolder = sha1(rand().microtime());
                $token = $tmpFolder;
                $set = Set::where('id', '=', $requestedSet)
                        ->with('group')
                        ->first();
                $setName = str_replace(" ", "_", $set->name);
                $grpName = str_replace(" ", "_", $set->group->name);
                $exportFileName = $setName."_".$grpName."_".time();

                $oldTokens = Token::where('set_id', '=', $requestedSet)->get();
                $BDDtoken = $this->saveTmpFolder($token,$tmpFolder."/".$exportFileName.".zip",$db,$requestedSet,"bbox");
                
                mkdir($tmpFolderPath.$tmpFolder, 0700);
                $filename = ($tmpFolderPath.$tmpFolder."/".$exportFileName.".zip");

                set_time_limit(0);
                ####################### ZIP CREATE  + COUNT INFOS ###################################
                $zip = new \ZipArchive();
                $zip->open($filename, \ZipArchive::CREATE);
                $allfilenamePath = $tmpFolderPath.$tmpFolder."/filename.txt";
                $allFilename = fopen($allfilenamePath, "w") or die("Unable to open file!");
                /* fetch object array */
                $nbrImages = 0;
                $nbrAreas = 0;
                $areasPerType = [];
                foreach ($imgToExport as $NImage) {
                    $imgToExportPath = "efs/img/".$NImage->path;
                    $path_parts = pathinfo($NImage->path);
                    $txtpath = $tmpFolderPath.$tmpFolder."/".$path_parts['filename'] .".txt";
                    $txtfile = fopen($txtpath, "w") or die("Unable to open file!");

                    //Building txt file with area data
                    $imgAreas = ImgArea::with('category')
                                ->where('source', $NImage->id)
                                ->where('state', 3)
                                ->get();
      
                    foreach ($imgAreas as $imgArea) {
                        $category = $imgArea->category->Category;
                        $category_ = str_replace(" ", "_", $category);
                        $line = $category_." 0 0 0 ".$imgArea->rectLeft." ".$imgArea->rectTop." ".$imgArea->rectRight." ".$imgArea->rectBottom." 0 0 0 0 0 0 0";
                        fwrite($txtfile, $line);
                        fwrite($txtfile, "\n");
                        //Counting Areas
                        $nbrAreas++;
                        if($areasPerType[$category]){
                            $areasPerType[$category]++;
                        }else{
                            $areasPerType[$category] = 1;
                        }
                    }
                    
                    //Closing txt file with polygon data
                    fclose($txtfile);


                    //Completing Zip
                    $zip->addFile($txtpath, $path_parts['filename'] .".txt");
                    $zip->addFile($imgToExportPath, $path_parts['filename'].".jpeg");
                    fwrite($allFilename, $NImage->path.",".$NImage->originalName."\n");

                    //Counting images
                    $nbrImages++;
                }
                fclose($allFilename);
                $zip->addFile($allfilenamePath, "filename.txt");
                $zip->close();
                ####################### ZIP  ###################################
                set_time_limit(120);

                //Unlink all textfile
                $files = glob($tmpFolderPath.$tmpFolder.'/*.{txt}', GLOB_BRACE);
                foreach($files as $file) {
                  unlink($file);
                }

                //Fill Zip info in bdd
                $BDDtoken->size = filesize($tmpFolderPath.$BDDtoken->archivePath);
                $BDDtoken->user = $currentUser->user_name;
                $BDDtoken->nbrImages = $nbrImages;
                $BDDtoken->nbrAreas = $nbrAreas;
                $BDDtoken->nbrAreas_per_type = json_encode($areasPerType);
                $BDDtoken->save();

                foreach ($oldTokens as $oldToken){
                    //Delete old token of set (in bdd) and zip file (in folder)
                    //folder
                    $this->rrmdir($tmpFolderPath.$oldToken->token);
                    //bdd
                    $oldToken->delete();
                }
                $dlInfos = Token::where('id', '=', $BDDtoken->id)->first();
                $res=array("msg"=>"Download Ready","link"=>$dlInfos->token,"size"=>$dlInfos->size,"user"=>$dlInfos->user,"dateGen"=>$dlInfos->date_generated,
                    "nbrImgs"=>$dlInfos->nbrImages,"nbrAreas"=>$dlInfos->nbrAreas,"areaPerType"=>$dlInfos->nbrAreas_per_type);
                echo json_encode($res);
                
            }
            else
                echo json_encode("No file found");
            
        }
        else // $_POST is empty.
        {
            echo json_encode("No data");
        }
        $this->cleanExport($db);
    }

    /**
     * Prepare a segimage Zip file to be download.
     *
     * Request type: POST
     */
    public function prepareSegZip($request, $response, $args)
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

        $tmpFolderPath = "efs/tmp/";

        // Get parameters: 
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);

        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);

        if(!array_key_exists ('category',$data)) $data->category = [];
        if(!array_key_exists ('groups',$data)) $data->groups = [1];
        $requestedSet = $data->setID;
        if($requestedSet == null) $requestedSet = 1;
        error_log(print_r($data,true));
        if (!empty($data))
        {
            $imgToExport = SegImage::where ('state', '=', 3)
                                ->whereIn('set_id', $validSet)
                                ->where ('set_id', '=', $requestedSet)
                                ->get();
            
            if (count($imgToExport) > 0) {
                
                $tmpFolder = sha1(rand().microtime());
                $token = $tmpFolder;
                $set = SegSet::where('id', '=', $requestedSet)
                        ->with('group')
                        ->first();
                $setName = str_replace(" ", "_", $set->name);
                $grpName = str_replace(" ", "_", $set->group->name);
                $exportFileName = $setName."_".$grpName."_".time();

                $oldTokens = Token::where('segset_id', '=', $requestedSet)->get();
                $BDDtoken = $this->saveTmpFolder($token,$tmpFolder."/".$exportFileName.".zip",$db,$requestedSet,"segmentation");
                
                mkdir( $tmpFolderPath.$tmpFolder, 0700);
                $filename = ( $tmpFolderPath.$tmpFolder."/".$exportFileName.".zip");

                set_time_limit(0);
                ####################### ZIP CREATE  + COUNT INFOS ###################################
                $zip = new \ZipArchive();
                $zip->open($filename, \ZipArchive::CREATE);
                
                $allfilenamePath =  $tmpFolderPath.$tmpFolder."/filename.txt";
                $allFilename = fopen($allfilenamePath, "w") or die("Unable to open file!");
                
                /* fetch object array */
                $nbrImages = 0;
                $nbrAreas = 0;
                $areasPerType = [];
                foreach ($imgToExport as $NImage) {
                    $imgToExportPath = "efs/img/segmentation/".$NImage->path;
                    $path_parts = pathinfo($NImage->path);
                    $pngpath =  $tmpFolderPath.$tmpFolder."/".$path_parts['filename'] .".png";
                    $txtpath =  $tmpFolderPath.$tmpFolder."/".$path_parts['filename'] .".txt";
                    $txtfile = fopen($txtpath, "w") or die("Unable to open file!");
                    $size = getimagesize($imgToExportPath);
                    $im = @imagecreate($size[0], $size[1])
                        or die("Cannot Initialize new GD image stream");
                    $background_color = imagecolorallocate($im, 0, 0, 0);
                    
                    //Building segmentation image 
                    $imgAreas = SegArea::with('category')
                                ->where('source', $NImage->id)
                                ->get();
      
                    foreach ($imgAreas as $imgArea) {
                        $arrPoly = json_decode (unserialize($imgArea->data));
                        $arrPoly2 = call_user_func_array('array_merge', $arrPoly);
                        $hexColor = $imgArea->category->Color;//"#ffffff";
                        $hex = ltrim($hexColor,'#');
                        $r = hexdec(substr($hex,0,2));
                        $g = hexdec(substr($hex,2,2));
                        $b = hexdec(substr($hex,4,2));

                        $blue = imagecolorallocate($im, $r, $g, $b);
                        imagefilledpolygon($im, $arrPoly2, count($arrPoly), $blue);
                        $col_poly = imagecolorallocate($im, 255, 255, 255);
                        imagesetthickness($im, 3);
                        imagepolygon($im, $arrPoly2, count($arrPoly),$col_poly);
                    }
                    //Save segmentation image 
                    header('Content-Type: image/png');
                    imagepng($im,$pngpath);
                    imagedestroy($im);

                    //Building txt file with polygon data
                    $imgAreas = SegArea::with('category')
                                ->where('source', $NImage->id)
                                ->get();
      
                    foreach ($imgAreas as $imgArea) {
                        $category = $imgArea->category->Category;
                        $category_ = str_replace(" ", "_", $category);
                        $line = $category_." ".$imgArea->data;
                        fwrite($txtfile, $line);
                        fwrite($txtfile, "\n");

                        //Counting Areas
                        $nbrAreas++;
                        if($areasPerType[$category]){
                            $areasPerType[$category]++;
                        }else{
                            $areasPerType[$category] = 1;
                        }
                    }
                    
                    //Closing txt file with polygon data
                    fclose($txtfile);


                    //Completing Zip
                    $zip->addFile($txtpath, $path_parts['filename'] .".txt");
                    $zip->addFile($pngpath, $path_parts['filename'] .".png");
                    $zip->addFile($imgToExportPath, $path_parts['filename'].".jpeg");
                    fwrite($allFilename, $NImage->path.",".$NImage->originalName."\n");

                    //Counting images
                    $nbrImages++;
                }

                fclose($allFilename);
                $zip->addFile($allfilenamePath, "filename.txt");
                $zip->close();
                ####################### ZIP  ###################################
                set_time_limit(120);
               

                $files = glob( $tmpFolderPath.$tmpFolder.'/*.{txt}', GLOB_BRACE);
                foreach($files as $file) {
                  unlink($file);
                }

                //Fill Zip info in bdd
                $BDDtoken->size = filesize( $tmpFolderPath.$BDDtoken->archivePath);
                $BDDtoken->user = $currentUser->user_name;
                $BDDtoken->nbrImages = $nbrImages;
                $BDDtoken->nbrAreas = $nbrAreas;
                $BDDtoken->nbrAreas_per_type = json_encode($areasPerType);
                $BDDtoken->save();

                foreach ($oldTokens as $oldToken){
                    //Delete old token of set (in bdd) and zip file (in folder)
                    //folder
                    $this->rrmdir("tmp/".$oldToken->token);
                    //bdd
                    $oldToken->delete();
                }
                
                $dlInfos = Token::where('id', '=', $BDDtoken->id)->first();
                $res=array("msg"=>"Download Ready","link"=>$dlInfos->token,"size"=>$dlInfos->size,"user"=>$dlInfos->user,"dateGen"=>$dlInfos->date_generated,
                    "nbrImgs"=>$dlInfos->nbrImages,"nbrAreas"=>$dlInfos->nbrAreas,"areaPerType"=>$dlInfos->nbrAreas_per_type);
                echo json_encode($res);
                
            }
            else
                echo json_encode("No file found");
            
        }
        else // $_POST is empty.
        {
            echo json_encode("No data");
        }
        $this->cleanExport($db);
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
        $tmpPath = "efs/tmp/";
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
         
        ob_end_flush();
        ob_get_flush();
        print readfile($filename);

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

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

        $validGroup = ['NULL'];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }

        //include('UploadHandler.php');
        error_reporting(E_ALL | E_STRICT);
        $upload_handler = new UploadHandler($this->ci,array(
            'groups' => $validGroup
            ));

    }

    public function uploadHandlerPublic($request, $response, $args)
    {
        /** @var UserFrosting\Sprinkle\Account\Authenticate\Authenticator $authenticator */
        /*$authenticator = $this->ci->authenticator;
        if (!$authenticator->check()) {
            $loginPage = $this->ci->router->pathFor('login');
            return $response->withRedirect($loginPage, 400);
        }*/

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        //$authorizer = $this->ci->authorizer;
        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        //$currentUser = $this->ci->currentUser;
        // Access-controlled page
        /*if (!$authorizer->checkAccess($currentUser, 'uri_upload')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }*/

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        //$classMapper = $this->ci->classMapper;

        /*$UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();
        */
        error_log(print_r($_FILES,true));
        return;
        $validGroup = [1];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }

        //include('UploadHandler.php');
        error_reporting(E_ALL | E_STRICT);
        $upload_handler = new UploadHandler($this->ci,array(
            'groups' => $validGroup,
            'param_name' => 'yo'
            ));

    }

    /**
     * Renders a simple "segupload" page for Users.
     *
     * Request type: GET
     */
    public function segUploadHandler($request, $response, $args)
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

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

        $validGroup = ['NULL'];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }

        //include('UploadHandler.php');
        error_reporting(E_ALL | E_STRICT);
        $upload_handler = new UploadHandler($this->ci,array(
            'script_url' => '/admin/segUpload/upload',
            'upload_dir' => '/efs/img/segmentation/',
            'upload_url' => '/efs/img/segmentation/',
            'imageMode' => 'segmentation',
            'groups' => $validGroup
            ));

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

    private function saveTmpFolder($token, $archivePath,$db,$setId,$mode){
        $aliveTimeHour = 10;// In years
        //$expires = date('Y-m-d H:i:s', $aliveTime);
        $expires = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m"),   date("d"),   date("Y")+$aliveTimeHour));
        $BDDtoken = new Token;
        $BDDtoken->token = $token;
        $BDDtoken->archivePath = $archivePath;
        $BDDtoken->expires = $expires;
        if($mode == "segmentation"){
            $BDDtoken->segset_id = $setId;
        }else{
            $BDDtoken->set_id = $setId;            
        }
        
        $BDDtoken->save();
        return $BDDtoken;
    }

    private function cleanExport($db){
        //clean tmp folder
        $dir    = 'efs/tmp';
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
                            if(file_exists ($dir.$token->token))
                                $this->rrmdir($dir.$token->token);
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
                    if(file_exists ($dir.$folder))
                        $this->rrmdir($dir.$folder);
            }
        }
        $sql = "SELECT `token`,`expires` FROM `labelimgexportlinks` WHERE 1";
        $tokens = $db->query($sql);
        while ($token = $tokens->fetch_object()) {
            if(date('Y-m-d H:i:s') > $token->expires ){
                $sql = "DELETE FROM `labelimgexportlinks` WHERE token = '$token->token'";   
                if ($db->query($sql) === TRUE) {
                    error_log("Clean DB: ".$token->token);
                    if(file_exists ($dir.$token->token))
                        $this->rrmdir($dir.$token->token);
                } else {
                    echo "Error: " . $sql . "<br>" . $db->error;
                }
            }
        }
    }

    public function tutoTranslate($request, $response, $args){

        // Get parameters
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);


        $config = $this->ci->config;
        $config['site.locales.selector'] = $data->locale;


        $translator = $this->ci->translator;
        
        return $this->ci->view->render($response, 'pages/tutorial.html.twig');
        
    }
    public function labelTranslate($request, $response, $args){

        // Get parameters
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);


        $config = $this->ci->config;
        $config['site.locales.selector'] = $data->locale;


        $translator = $this->ci->translator;
        
        return $this->ci->view->render($response, 'pages/label.html.twig');
        
    }
    public function indexTranslate($request, $response, $args){

        // Get parameters
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);


        $config = $this->ci->config;
        $config['site.locales.selector'] = $data->locale;


        $translator = $this->ci->translator;
        
        return $this->ci->view->render($response, 'pages/index.html.twig');
        
    }
    public function archiveUploadKeepProgress($request, $response, $args){
        if (array_key_exists("upload_status",$_SESSION)){
            if(array_key_exists($this->ci->currentUser->id,$_SESSION['upload_status'])){
                $array = $_SESSION['upload_status'][$this->ci->currentUser->id];
            }
            else{
                $array = [];
            }
        }
        else{
            $array = [];
        }

        $data = json_encode($array);
        
        return $response
            ->withHeader("Content-Type", "text/event-stream")
            ->withHeader("Cache-Control", "no-cache")
            ->write("retry: 1000\ndata: {$data}\n\n");
        
    }
}