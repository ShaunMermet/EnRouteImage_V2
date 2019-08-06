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
use UserFrosting\Sprinkle\Site\Database\Models\Set;
use UserFrosting\Sprinkle\Site\Database\Models\SegSet;
use UserFrosting\Sprinkle\Site\Database\Models\ImgCategories;
use UserFrosting\Sprinkle\Site\Database\Models\ImgLinks;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\ServerSideValidator;

/**
 * Controller class for category-related requests.
 *
 * @author 
 * @see 
 */
class SetController extends SimpleController
{
	/**
     * Returns list of my sets.
     *
     * This function requires authentication.
     * Request type: GET
     */
    public function getMySets($request, $response, $args)
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

        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

        $validGroup = [];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }
        $sets = Set::whereIn('group_id', $validGroup)
        			->with('group')
                    ->get();
        
        $result = $sets->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }
    /**
     * Returns list of my segsets.
     *
     * This function requires authentication.
     * Request type: GET
     */
    public function getMySegSets($request, $response, $args)
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

        $UserWGrp = $classMapper->staticMethod('user', 'where', 'id', $currentUser->id)
                                ->with('group')
                                ->first();

        $validGroup = [];
        foreach ($UserWGrp->group as $group) {
            array_push($validGroup, $group->id);
        }
        $sets = SegSet::whereIn('group_id', $validGroup)
                    ->with('group')
                    ->get();
        
        $result = $sets->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        //return $sprunje->toResponse($response);
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }
    public function getMySetsNA($request, $response, $args)
    {
        $validGroup = [1];
        $sets = Set::whereIn('group_id', $validGroup)
                    ->with('group')
                    ->get();
        
        $result = $sets->toArray();

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        //return $sprunje->toResponse($response);
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }



	/**
     * Edit a set.
     *
     * This function requires authentication.
     * Request type: PUT
     */
    public function editSet($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_setEdit')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        // Get PUT parameters: (name, slug, icon, description)
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);

        $mode = $data->mode;
        $setId = $data->setId;
        $setName = $data->setName;
        $setGroup = $data->setGroup;

        if ($mode == "CREATE"){
            if ($data->setName == ""){
                error_log ("no label");
                echo "FAIL";
                exit;
            }
            
            //TODO Insert
            $set = new Set;
            $set->name = $setName;
            $set->group_id = $setGroup;
            $set->save();
            
        }else if ($mode == "EDIT"){
            if(intval($setId) < 1){
                error_log ("wrong ID");
                echo "FAIL";
                exit;
            }
            $set = Set::where('id', $setId)->first();
            $set->name = $setName;
            $set->group_id = $setGroup;
            $set->save();
        }else if ($mode == "DELETE"){
            if(intval($setId) < 1){
                error_log ("wrong ID");
                echo "FAIL";
                exit;
            }
            $set = Set::where('id', $setId)->first();
            $set->delete();
            
        }else{
            error_log ("wrong mode");
            echo "FAIL";
        }
    }

    /**
     * Edit a set.
     *
     * This function requires authentication.
     * Request type: PUT
     */
    public function editSegSet($request, $response, $args)
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
        if (!$authorizer->checkAccess($currentUser, 'uri_setEdit')) {
            $loginPage = $this->ci->router->pathFor('login');
           return $response->withRedirect($loginPage, 400);
        }

        // Get PUT parameters: (name, slug, icon, description)
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);

        $mode = $data->mode;
        $setId = $data->setId;
        $setName = $data->setName;
        $setGroup = $data->setGroup;

        if ($mode == "CREATE"){
            if ($data->setName == ""){
                error_log ("no label");
                echo "FAIL";
                exit;
            }
            
            //TODO Insert
            $set = new SegSet;
            $set->name = $setName;
            $set->group_id = $setGroup;
            $set->save();
            
        }else if ($mode == "EDIT"){
            if(intval($setId) < 1){
                error_log ("wrong ID");
                echo "FAIL";
                exit;
            }
            $set = SegSet::where('id', $setId)->first();
            $set->name = $setName;
            $set->group_id = $setGroup;
            $set->save();
        }else if ($mode == "DELETE"){
            if(intval($setId) < 1){
                error_log ("wrong ID");
                echo "FAIL";
                exit;
            }
            $set = SegSet::where('id', $setId)->first();
            $set->delete();
            
        }else{
            error_log ("wrong mode");
            echo "FAIL";
        }
    }

    /**
     * Get the info corresponding to one set
     *
     * This page requires authentication.
     * Request type: GET
     */
    public function getSetDlInfos($request, $response, $args)
    {
        error_log("in getSetDlInfos");
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
        $setMode = $params["setMode"];
        
        if($requestedSet == null) $requestedSet = 1;
        if($setMode == "segmentation"){
            $set = SegSet::where('id', '=', $requestedSet)
                    ->with('token')
                    ->first();
        }else{
            $set = Set::where('id', '=', $requestedSet)
                    ->with('token')
                    ->first();
        }
        
        $dlInfos = $set->token;

        //parse model files
        $trainFolderPath = "efs/train/";
        $tmpTrainfolderName = $trainFolderPath.$dlInfos->token;
        $fileList = glob($tmpTrainfolderName.'/output/*.weights');
        $modelList = [];
        foreach($fileList as $filename){
            if(is_file($filename)){
                $model = array(
                    "filename"=>basename($filename),
                    "filesize"=>filesize($filename),
                );
                array_push($modelList, $model);
            }   
        }
            
        $res=array("msg"=>"Download Ready","link"=>$dlInfos->token,"size"=>$dlInfos->size,"user"=>$dlInfos->user,"dateGen"=>$dlInfos->date_generated,
            "nbrImgs"=>$dlInfos->nbrImages,"nbrAreas"=>$dlInfos->nbrAreas,"areaPerType"=>$dlInfos->nbrAreas_per_type,"modelList"=>$modelList);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $response->withJson($res, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Train a set.
     *
     * Request type: POST
     */
    public function trainSet($request, $response, $args)
    {
        error_log("in trainSet");
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

        // Get parameters: 
        $params = $request->getParsedBody();
        $data = json_decode(json_encode($params), FALSE);
        
        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);

        if(!array_key_exists ('category',$data)) $data->category = [];
        if(!array_key_exists ('groups',$data)) $data->groups = [1];
        $requestedSet = $data->setID;
        $nbrIteration = $data->nbrStep;
        if($requestedSet == null) $requestedSet = 1;

        $cats = ImgCategories::whereIn('set_id', [$requestedSet])
                    //->with('set')
                    ->get();
        $catArray = $cats->toArray();

        error_log(print_r($data,true));
        error_log("catArray");
        error_log(print_r($catArray,true));


        if (!empty($data))
        {
            //tmp to change
            /** @var UserFrosting\Config\Config $config */
            $config = $this->ci->config['db.default'];
            $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);
            $tmpPath = "efs/tmp/";
            $token = mysqli_real_escape_string($db,$data->token);
            error_log(print_r($token,true));
            $sql = "SELECT exlk.archivePath FROM labelimgexportlinks exlk WHERE exlk.token = '$token'";
            $res = $db->query($sql);
            $tmpLink = $res->fetch_object();
            $tmpfolderName = $tmpPath.$token;

            //create set folder to work in
            $trainFolderPath = "efs/train/";
            $tmpTrainfolderName = $trainFolderPath.$token;
            mkdir( $tmpTrainfolderName, 0700);
            error_log(print_r($tmpTrainfolderName,true));
            
            //record folder in bdd
                //maybe skip that


            //generate voc.names
            $vocpath =  $tmpTrainfolderName."/voc.names";
            $vocfile = fopen($vocpath, "w") or die("Unable to open file!");
                
            //List of all categories
            foreach ($catArray as $cat) {
                //write in file
                fwrite($vocfile, $cat["Category"]);
                fwrite($vocfile, "\n");
            }
            fclose($vocfile);

            //generate voc.data
            $vocDatapath =  $tmpTrainfolderName."/voc.data";
            $vocDatafile = fopen($vocDatapath, "w") or die("Unable to open file!");
            
            fwrite($vocDatafile, "classe = ".sizeof($catArray));
            fwrite($vocDatafile, "\n");
            fwrite($vocDatafile, "train = ".$tmpTrainfolderName."/train.txt");
            fwrite($vocDatafile, "\n");
            fwrite($vocDatafile, "valid = valid.txt");
            fwrite($vocDatafile, "\n");
            fwrite($vocDatafile, "names = voc.names");
            fwrite($vocDatafile, "\n");
            //fwrite($vocDatafile, "backup = output");
            fwrite($vocDatafile, "backup = ".$tmpTrainfolderName."/output/");
            

            //generate personalized cfg file (NN structure)
            $baseModelPath = $trainFolderPath."cfg/"."yolov3-tiny.cfg";
            $destModelPath = $tmpTrainfolderName."/yolov3-tiny.cfg";
            copy($baseModelPath, $destModelPath);
            $modelData = file_get_contents($destModelPath);
            $arrModelData = explode("\r\n", $modelData);
            function customSearch($keyword, $arrayToSearch){
                foreach($arrayToSearch as $key => $arrayItem){
                    if( stristr( $arrayItem, $keyword ) ){
                        return $key;
                    }
                }
            }
            $mbKey = customSearch("max_batches", $arrModelData);
            $arrModelData[$mbKey] = "max_batches = ".$nbrIteration;
            $modelData = implode("\r\n", $arrModelData);
            file_put_contents($destModelPath, $modelData);


            //Create output folder
            mkdir( $tmpTrainfolderName."/output", 0700);

            
            //generate train.txt
            //file with all the pictures name
            $imgToTrain = ImgLinks::where ('state', '=', 3)
                            ->whereIn('set_id', $validSet)
                            ->where ('set_id', '=', $requestedSet)
                            ->get();
            if (count($imgToTrain) > 0) {
                $allfilenamePath = $tmpTrainfolderName."/train.txt";
                $allFilename = fopen($allfilenamePath, "w") or die("Unable to open file!");
            }
            foreach ($imgToTrain as $NImage) {
                fwrite($allFilename, $tmpfolderName."/".$NImage->path."\n");
            }

            


            error_log("set to train = ");
            error_log(print_r($requestedSet,true));
            //$nbrIteration = 10;
            session_start();
            $_SESSION['train_status'][$this->ci->currentUser->id][$token]["train_progress"] = 0;
            $_SESSION['train_status'][$this->ci->currentUser->id][$token]["iteration_nbr"] = 0;
            $_SESSION['train_status'][$this->ci->currentUser->id][$token]["iteration_max"] = 0;
            session_write_close();

            $cmd = "darknet detector train ".$tmpTrainfolderName."/voc.data efs/train/".$token."/yolov3-tiny.cfg efs/train/cfg/darknet53.conv.74";
            
            //$cmd = "darknet";
            set_time_limit(0);

            while (@ ob_end_flush()); // end all output buffers if any

            $proc = popen($cmd,'r');

            $live_output     = "";
            $complete_output = "";
            //error_log(print_r(fgets($proc, 1024),true));
            while (!feof($proc))
            {
                $live_output     = fgets($proc, 4096);
                $complete_output = $complete_output . $live_output;
                if (strpos($live_output, 'images') !== false) {
                    //error_log(print_r($live_output,true));
                    $a = explode(":",$live_output);
                    $iterationNumber = $a[0];
                    //error_log(print_r($iterationNumber,true));
                    $currentProgress = round($iterationNumber/$nbrIteration*100);
                    //error_log(print_r($currentProgress,true));
                    session_start();
                    $_SESSION['train_status'][$this->ci->currentUser->id][$token]["train_progress"] = $currentProgress;
                    $_SESSION['train_status'][$this->ci->currentUser->id][$token]["iteration_nbr"] = $iterationNumber;
                    $_SESSION['train_status'][$this->ci->currentUser->id][$token]["iteration_max"] = $nbrIteration;
                    session_write_close();
                }
                
                //sleep(1);
                //@ flush();
            }

            pclose($proc);

            set_time_limit(120);
            
            //Reset to initial state
            session_start();
            $_SESSION['train_status'][$this->ci->currentUser->id][$token]["train_progress"] = 0;
            $_SESSION['train_status'][$this->ci->currentUser->id][$token]["iteration_nbr"] = 0;
            $_SESSION['train_status'][$this->ci->currentUser->id][$token]["iteration_max"] = 0;
            session_write_close();

            //error_log(print_r($output,true));

            $res=array("msg"=>"Model Ready","link"=>"link");
                echo json_encode($res);
        }
        else // $_POST is empty.
        {
            echo json_encode("No data");
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

        
        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);

        error_log("setC : enter dl");

        if(!isset($args['dl_id'])) {
            error_log("setC : No token");
            exit;
        }
        else{
            error_log($args['dl_id']);
        }
        error_log("setC : etape 1");
        $tmpPath = "efs/tmp/";
        $token = mysqli_real_escape_string($db,$args['dl_id']);
        $dlFilename = mysqli_real_escape_string($db,$args['dl_filename']);
        $outputPath = "efs/train/".$token."/output/";
        //$sql = "SELECT exlk.archivePath FROM labelimgexportlinks exlk WHERE exlk.token = '$token'";
        //$res = $db->query($sql);
        //$tmpLink = $res->fetch_object();

        //$count = mysqli_num_rows($res);
        //error_log($count);
        //if($count != 1) 
            //exit;
        error_log("setC : etape 2");

        $filename = $outputPath. $dlFilename;

        if (!file_exists($filename)) 
            exit;
        error_log("setC : etape 3");
        error_log($filename);
        // send $filename to browser
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filename);
        $size = filesize($filename);
        $name = basename($filename);
         error_log("setC : etape 4");
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
     * delete file requested from set
     *
     * Request type: post
     */
    public function deleteModel($request, $response, $args)
    {
        error_log("in deleteModel");
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

        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);

        if(!isset($args['dl_id'])) {
            error_log("deleteModel : No token");
            exit;
        }
        else{
            //error_log($args['dl_id']);
        }

        $token = mysqli_real_escape_string($db,$args['dl_id']);
        $dlFilename = mysqli_real_escape_string($db,$args['dl_filename']);
        $outputPath = "efs/train/".$token."/output/";
        $filename = $outputPath. $dlFilename;
        if (!file_exists($filename)) 
            exit;
        error_log("delete file");
        error_log(print_r($filename,true));
        if (file_exists($filename)) {
            unlink($filename);
        } else {
            // File not found.
        }
    }

    /**
     * Renders the modal form for editing an existing user.
     *
     * This does NOT render a complete page.  Instead, it renders the HTML for the modal, which can be embedded in other pages.
     * This page requires authentication.
     * Request type: GET
     */
    public function getModalEditModel($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Get a list of all groups
        $groups = $classMapper->staticMethod('group', 'all');

        /** @var Config $config */
        $config = $this->ci->config;

        // Get a list of all locales
        $locales = $config['site.locales.available'];

        // Generate form
        $fields = [
            'hidden' => ['theme'],
            'disabled' => ['user_name']
        ];
        
        $token = $params['token'];
        $dlFilename = $params['filename'];
         // Load validation rules
        $schema = new RequestSchema('schema://aimodel/edit-info.json');
        $validator = new JqueryValidationAdapter($schema, $this->ci->translator);
        $translator = $this->ci->translator;
        return $this->ci->view->render($response, 'components/modals/aimodel.html.twig', [
            'aimodel' => [
                'filename' => $dlFilename,
                'token' => $token
            ],
            'form' => [
                'action' => "api/sets/aiModel/edit/save",
                'method' => 'PUT',
                'submit_text' => $translator->translate("UPDATE")
            ],
            'page' => [
                //'validators' => $validator->rules('json', false)
            ]
        ]);
    }

    /**
     * Processes the request to update an existing model file name
     *
     * Processes the request from the model update form, checking that:
     * 2. The logged-in user has the necessary permissions to update the putted field(s);
     * 3. The submitted data is valid.
     * This route requires authentication.
     * Request type: PUT
     */
    public function updateModelInfo($request, $response, $args)
    {
        /** @var Config $config */
        $params = $request->getQueryParams();

        /** @var UserFrosting\Config\Config $config */
        $config = $this->ci->config['db.default'];
        $db = mysqli_connect($config['host'],$config['username'],$config['password'],$config['database']);

        // Get PUT parameters
        $params = $request->getParsedBody();

        /** @var MessageStream $ms */
        $ms = $this->ci->alerts;

        // Load the request schema
        //$schema = new RequestSchema('schema://user/edit-info.json');

        $dlFilename = $params["last_name"];
        $newFilename = $params["first_name"];
        $token = $params["token"];
        $outputPath = "efs/train/".$token."/output/";
        $filename = $outputPath. $dlFilename;
        $newFilePath = $outputPath.$newFilename;
       
        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;


        if (file_exists($filename) && 
            ((!file_exists($newFilePath)) || is_writable($newFilePath))) {
            rename($filename, $newFilePath);
            error_log("try rename");
        }
        
        return $response->withStatus(200);
    }
}