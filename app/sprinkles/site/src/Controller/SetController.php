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
        $res=array("msg"=>"Download Ready","link"=>$dlInfos->token,"size"=>$dlInfos->size,"user"=>$dlInfos->user,"dateGen"=>$dlInfos->date_generated,
            "nbrImgs"=>$dlInfos->nbrImages,"nbrAreas"=>$dlInfos->nbrAreas,"areaPerType"=>$dlInfos->nbrAreas_per_type);

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
            $token = mysqli_real_escape_string($db,$args['dl_id']);
            $sql = "SELECT exlk.archivePath FROM labelimgexportlinks exlk WHERE exlk.token = '$token'";
            $res = $db->query($sql);
            $tmpLink = $res->fetch_object();
            $tmpfolderName = $tmpPath.$tmpLink->archivePath;

            //create set folder to work in
            $trainFolderPath = "efs/train/";
            mkdir( $trainFolderPath.$requestedSet, 0700);
            
            //record folder in bdd
                //maybe skip that


            //generate voc.names
            $vocpath =  $trainFolderPath.$requestedSet."/voc.names";
            $vocfile = fopen($vocpath, "w") or die("Unable to open file!");
                
            //List of all categories
            foreach ($catArray as $cat) {
                //write in file
                fwrite($vocfile, $cat["Category"]);
                fwrite($vocfile, "\n");
            }
            fclose($txtfile);

            //generate voc.data
            $vocDatapath =  $trainFolderPath.$requestedSet."/voc.data";
            $vocDatafile = fopen($vocDatapath, "w") or die("Unable to open file!");
            
            fwrite($vocDatafile, "classe = ".sizeof($catArray));
            fwrite($vocDatafile, "\n");
            fwrite($vocDatafile, "train = ".$trainFolderPath.$requestedSet."/train.txt");
            fwrite($vocDatafile, "\n");
            fwrite($vocDatafile, "valid = valid.txt");
            fwrite($vocDatafile, "\n");
            fwrite($vocDatafile, "names = voc.names");
            fwrite($vocDatafile, "\n");
            //fwrite($vocDatafile, "backup = output");
            fwrite($vocDatafile, "backup = ".$trainFolderPath.$requestedSet."/output/");
            
            //Create output folder
            mkdir( $trainFolderPath.$requestedSet."/output", 0700);

            
            //generate train.txt
            //file with all the pictures name
            $imgToTrain = ImgLinks::where ('state', '=', 3)
                            ->whereIn('set_id', $validSet)
                            ->where ('set_id', '=', $requestedSet)
                            ->get();
            if (count($imgToTrain) > 0) {
                $allfilenamePath = $trainFolderPath.$requestedSet."/train.txt";
                $allFilename = fopen($allfilenamePath, "w") or die("Unable to open file!");
            }
            foreach ($imgToTrain as $NImage) {
                fwrite($allFilename, $tmpfolderName."/".$NImage->path."\n");
            }

            


            error_log("set to train = ");
            error_log(print_r($requestedSet,true));
            $nbrIteration = 10;
            session_start();
            $_SESSION['train_status'][$this->ci->currentUser->id][$requestedSet]["train_progress"] = 0;
            session_write_close();

            $cmd = "darknet detector train efs/train/".$requestedSet."/voc.data efs/train/cfg/yolov3-tiny.cfg efs/train/cfg/darknet53.conv.74";
            
            //$cmd = "darknet";
            set_time_limit(0);
            //ob_implicit_flush(true);
            //exec($cmd, $output, $error);
            
            /*$cwd = null;
            $descriptors = array(
                0 => array('pipe', 'r'),
                1 => array("pipe", "w"),
                2 => array('pipe', 'w'),
            );*/



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
                    $_SESSION['train_status'][$this->ci->currentUser->id][$requestedSet]["train_progress"] = $currentProgress;
                    session_write_close();
                }
                
                //sleep(1);
                //@ flush();
            }

            pclose($proc);


            //$handle = proc_open($cmd, $descriptors, $pipes, $cwd);
            //stream_set_blocking($pipes[1], true);
            //if (is_resource($handle))
            //{
                /*$i=0;
                do {
                    $status = proc_get_status($handle);
                    // If our stderr pipe has data, grab it for use later.
                        if (!feof($pipes[1])) {
                          // We're acting like passthru would and displaying errors as they come in.
                          $Output_line = fgets($pipes[1]);
                          //echo $error_line;
                          error_log(print_r($Output_line,true));
                          //$stderr_ouput[] = $error_line; 
                        }
                    $return_message = fgets($pipes[1], 1024);
                    if (strlen($return_message) == 0) break;
                    error_log(print_r($return_message,true));
                    $i++;
                    sleep(1);
                } while ($status['running']);*/

                /*while( ! feof($pipes[1]))
                {
                    $return_message = fgets($pipes[1], 1024);
                    if (strlen($return_message) == 0) break;
                    $a = explode(":",$return_message);
                    $iterationNumber = $a[0];
                    //error_log(print_r($iterationNumber,true));
                    $currentProgress = round($iterationNumber/$nbrIteration*100);
                    error_log(print_r($currentProgress,true));
                    session_start();
                    $_SESSION['train_status'][$this->ci->currentUser->id][$requestedSet]["train_progress"] = $currentProgress;
                    session_write_close();
                    ob_flush();
                    flush();
                }*/
            //}
            /* $i = 0;
            while($i < 10){ 
                $currentProgress = round($i/$nbrIteration*100);
                error_log(print_r($currentProgress,true));
                session_start();
                $_SESSION['train_status'][$this->ci->currentUser->id][$requestedSet]["train_progress"] = $currentProgress;
                session_write_close();
                sleep(1);
                $i++;
            }*/
            /*fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            if (!$handle) {
                echo 'failed to run command';
            } else {
                proc_close($handle); // wait for command to finish (optional)
            }*/

            set_time_limit(120);
            /*$result = [];
            if ($error) {
                error_log("train exec error");
                error_log(print_r($error,true));
                error_log(print_r($output,true));
                return false;
            }else{
                $result["result"] = $output[0];



                //$dim = $output[1];
                //$result["x"] = $output[2];
                //$result["y"] = $output[1];
                //$find = array(",","[","]");
                //$replace = array("");
                //$result["data"] = str_replace($find,$replace,$output[3]);
                //$result["data"] = base64_encode(gzcompress($result["data"], 9, ZLIB_ENCODING_DEFLATE));
            }*/
            //Reset to initial state
            session_start();
            $_SESSION['train_status'][$this->ci->currentUser->id][$requestedSet]["train_progress"] = 0;
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
}