<?php

/*
 * jQuery File Upload Plugin PHP Class
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
namespace UserFrosting\Sprinkle\Site\Controller;

use UserFrosting\Sprinkle\Site\Database\Models\ImgCategories;
use UserFrosting\Sprinkle\Site\Database\Models\ImgArea;
use UserFrosting\Sprinkle\Site\Database\Models\ImgLinks;
use UserFrosting\Sprinkle\Site\Database\Models\SegImage;
use UserFrosting\Sprinkle\Site\Database\Models\SegCategory;
use UserFrosting\Sprinkle\Site\Database\Models\SegArea;
use UserFrosting\Sprinkle\Site\Database\Models\Set;
use UserFrosting\Sprinkle\Site\Database\Models\SegSet;
use UserFrosting\Sprinkle\Site\Sprunje\ImgLinksSprunje;
use UserFrosting\Sprinkle\Site\Sprunje\SegImageSprunje;
use Illuminate\Database\QueryException;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use Illuminate\Database\Capsule\Manager as Capsule;

class UploadHandler extends SimpleController
{
    protected $options;

    protected $ci;

    // PHP File Upload error message codes:
    // http://php.net/manual/en/features.file-upload.errors.php
    protected $error_messages = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
        'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size' => 'File is too big',
        'min_file_size' => 'File is too small',
        'accept_file_types' => 'Filetype not allowed',
        'max_number_of_files' => 'Maximum number of files exceeded',
        'max_width' => 'Image exceeds maximum width',
        'min_width' => 'Image requires a minimum width',
        'max_height' => 'Image exceeds maximum height',
        'min_height' => 'Image requires a minimum height',
        'abort' => 'File upload aborted',
        'image_resize' => 'Failed to resize image',
		'insert_db_failed' => 'Failed to insert in database',
		'duplicate_key' => 'File already uploaded'
    );

    protected $image_objects = array();

    public function __construct($ci = null,$options = null, $initialize = true, $error_messages = null) {
        $this->response = array();
        $this->options = array(
            'script_url' => $this->get_full_url().'/admin/upload/upload'/*.$this->basename($this->get_server_var('SCRIPT_NAME'))*/,
            'upload_dir' => dirname($this->get_server_var('SCRIPT_FILENAME')).'/efs/img/',
            'upload_url' => $this->get_full_url().'/efs/img/',
            'input_stream' => 'php://input',
            'user_dirs' => false,
            'mkdir_mode' => 0755,
            'param_name' => 'files',
            // Set the following option to 'POST', if your server does not support
            // DELETE requests. This is a parameter sent to the client:
            'delete_type' => 'DELETE',
            'access_control_allow_origin' => '*',
            'access_control_allow_credentials' => false,
            'access_control_allow_methods' => array(
                'OPTIONS',
                'HEAD',
                'GET',
                'POST',
                'PUT',
                'PATCH',
                'DELETE'
            ),
            'access_control_allow_headers' => array(
                'Content-Type',
                'Content-Range',
                'Content-Disposition'
            ),
            // By default, allow redirects to the referer protocol+host:
            'redirect_allow_target' => '/^'.preg_quote(
              parse_url($this->get_server_var('HTTP_REFERER'), PHP_URL_SCHEME)
                .'://'
                .parse_url($this->get_server_var('HTTP_REFERER'), PHP_URL_HOST)
                .'/', // Trailing slash to not match subdomains by mistake
              '/' // preg_quote delimiter param
            ).'/',
            // Enable to provide file downloads via GET requests to the PHP script:
            //     1. Set to 1 to download files via readfile method through PHP
            //     2. Set to 2 to send a X-Sendfile header for lighttpd/Apache
            //     3. Set to 3 to send a X-Accel-Redirect header for nginx
            // If set to 2 or 3, adjust the upload_url option to the base path of
            // the redirect parameter, e.g. '/files/'.
            'download_via_php' => false,
            // Read files in chunks to avoid memory limits when download_via_php
            // is enabled, set to 0 to disable chunked reading of files:
            'readfile_chunk_size' => 10 * 1024 * 1024, // 10 MiB
            // Defines which files can be displayed inline when downloaded:
            'inline_file_types' => '/\.(jpe?g|png|zip)$/i',
            // Defines which files (based on their names) are accepted for upload:
            'accept_file_types' => '/\.(jpe?g|png|zip)$/i',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => 12*1024*1024,
            'min_file_size' => 1,
            // The maximum number of files for the upload directory:
            'max_number_of_files' => null,
            // Defines which files are handled as image files:
            'image_file_types' => '/\.(jpe?g|png)$/i',
            // Use exif_imagetype on all files to correct file extensions:
            'correct_image_extensions' => false,
            // Image resolution restrictions:
            'max_width' => null,
            'max_height' => null,
            'min_width' => 1,
            'min_height' => 1,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => true,
            // Set to 0 to use the GD library to scale and orient images,
            // set to 1 to use imagick (if installed, falls back to GD),
            // set to 2 to use the ImageMagick convert binary directly:
            'image_library' => 1,
            // Uncomment the following to define an array of resource limits
            // for imagick:
            /*
            'imagick_resource_limits' => array(
                imagick::RESOURCETYPE_MAP => 32,
                imagick::RESOURCETYPE_MEMORY => 32
            ),
            */
            // Command or path for to the ImageMagick convert binary:
            'convert_bin' => 'convert',
            // Uncomment the following to add parameters in front of each
            // ImageMagick convert call (the limit constraints seem only
            // to have an effect if put in front):
            /*
            'convert_params' => '-limit memory 32MiB -limit map 32MiB',
            */
            // Command or path for to the ImageMagick identify binary:
            'identify_bin' => 'identify',
            'image_versions' => array(
                // The empty image version key defines options for the original image:
                '' => array(
                    // Automatically rotate images based on EXIF meta data:
                    'auto_orient' => true
                ),
                // Uncomment the following to create medium sized images:
                /*
                'medium' => array(
                    'max_width' => 800,
                    'max_height' => 600
                ),
                */
                'light' => array(
                ),
                'thumbnail' => array(
                    // Uncomment the following to use a defined directory for the thumbnails
                    // instead of a subdirectory based on the version identifier.
                    // Make sure that this directory doesn't allow execution of files if you
                    // don't pose any restrictions on the type of uploaded files, e.g. by
                    // copying the .htaccess file from the files directory for Apache:
                    //'upload_dir' => dirname($this->get_server_var('SCRIPT_FILENAME')).'/thumb/',
                    //'upload_url' => $this->get_full_url().'/thumb/',
                    // Uncomment the following to force the max
                    // dimensions and e.g. create square thumbnails:
                    //'crop' => true,
                    'max_width' => 80,
                    'max_height' => 80
                )
            ),
            'print_response' => true,
            'imageMode' => 'bbox',
            'groups' => [NULL,1]
        );
        if ($ci) {
            $this->ci = $ci;
        }
        session_start();
        if(!array_key_exists('upload_status',$_SESSION)){
            $_SESSION['upload_status'] = [];
        }
        if(!array_key_exists($this->ci->currentUser->id,$_SESSION['upload_status'])){
            $_SESSION['upload_status'][$this->ci->currentUser->id] = [];
        }
        session_write_close();
        if ($options) {
            $this->options = $options + $this->options;
            if(array_key_exists('script_url',$options)){
                $this->options['script_url'] = $this->get_full_url().$options['script_url'];
            }
            if(array_key_exists('upload_dir',$options)){
                $this->options['upload_dir'] = dirname($this->get_server_var('SCRIPT_FILENAME')).$options['upload_dir'];
            }
            if(array_key_exists('upload_url',$options)){
                $this->options['upload_url'] = $this->get_full_url().$options['upload_url'];
            }
            if(array_key_exists('groups',$options)){
                $this->options['groups'] = $options['groups'];
            }
        }
        if ($error_messages) {
            $this->error_messages = $error_messages + $this->error_messages;
        }
        if ($initialize) {
            $this->initialize();
        }
    }

    protected function initialize() {
        switch ($this->get_server_var('REQUEST_METHOD')) {
            case 'OPTIONS':
            case 'HEAD':
                $this->head();
                break;
            case 'GET':
                $this->get($this->options['print_response']);
                break;
            case 'PATCH':
            case 'PUT':
            case 'POST':
                error_log("upload as post");
                $this->post($this->options['print_response']);
                break;
            case 'DELETE':
                $this->delete($this->options['print_response']);
                break;
            default:
                $this->header('HTTP/1.1 405 Method Not Allowed');
        }
        ini_set('post_max_size', '10G');
        ini_set('upload_max_filesize', '10G');
    }

    protected function get_full_url() {
        $https = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') === 0 ||
            !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
        return
            ($https ? 'https://' : 'http://').
            (!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
            ($https && $_SERVER['SERVER_PORT'] === 443 ||
            $_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
            substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    protected function get_user_id() {
        @session_start();
        return session_id();
    }

    protected function get_user_path() {
        if ($this->options['user_dirs']) {
            return $this->get_user_id().'/';
        }
        return '';
    }

    protected function get_upload_path($file_name = null, $version = null) {
        $file_name = $file_name ? $file_name : '';
        if (empty($version)) {
            $version_path = '';
        } else {
            $version_dir = @$this->options['image_versions'][$version]['upload_dir'];
            if ($version_dir) {
                return $version_dir.$this->get_user_path().$file_name;
            }
            $version_path = $version.'/';
        }
        return $this->options['upload_dir'].$this->get_user_path()
            .$version_path.$file_name;
    }
    protected function get_upload_path_custom($file_id) {
        $bddFile = $this->get_file_bdd_by_id($file_id);
        return $bddFile['path'];
    }

    protected function get_query_separator($url) {
        return strpos($url, '?') === false ? '?' : '&';
    }

    protected function get_download_url($file_name, $version = null, $direct = false) {
        if (!$direct && $this->options['download_via_php']) {
            $url = $this->options['script_url']
                .$this->get_query_separator($this->options['script_url'])
                .$this->get_singular_param_name()
                .'='.rawurlencode($file_name);
            if ($version) {
                $url .= '&version='.rawurlencode($version);
            }
            return $url.'&download=1';
        }
        if (empty($version)) {
            $version_path = '';
        } else {
            $version_url = @$this->options['image_versions'][$version]['upload_url'];
            if ($version_url) {
                return $version_url.$this->get_user_path().rawurlencode($file_name);
            }
            $version_path = rawurlencode($version).'/';
        }
        return $this->options['upload_url'].$this->get_user_path()
            .$version_path.rawurlencode($file_name);
    }

    protected function set_additional_file_properties($file) {
        $file->deleteUrl = $this->options['script_url']
            .$this->get_query_separator($this->options['script_url'])
            .$this->get_singular_param_name()
            .'='.rawurlencode($file->imgID);
        $file->deleteType = $this->options['delete_type'];
        if ($file->deleteType !== 'DELETE') {
            $file->deleteUrl .= '&_method=DELETE';
        }
        if ($this->options['access_control_allow_credentials']) {
            $file->deleteWithCredentials = true;
        }
    }

    // Fix for overflowing signed 32 bit integers,
    // works for sizes up to 2^32-1 bytes (4 GiB - 1):
    protected function fix_integer_overflow($size) {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }
        return $size;
    }
    protected function get_file_group($file_setId){
        if($this->options['imageMode'] == 'segmentation'){
            $getGrp = SegSet::where('id',  $file_setId)
                    ->with('group')
                    ->first();
        }else{
            $getGrp = Set::where('id',  $file_setId)
                    ->with('group')
                    ->first();
        }
        if(!$getGrp) return '';
        $result = $getGrp->toArray();
        return $result['group']['name'];
        
    }
    protected function get_file_bdd($file_name){
        if($this->options['imageMode'] == 'segmentation'){
            $getSet = SegImage::where('path',  $file_name)
                                ->with('set')
                                ->first();
        }else{
            $getSet = ImgLinks::where('path',  $file_name)
                                ->with('set')
                                ->first();
        }
        if(!$getSet) return '';
        $result = $getSet->toArray();
        return $result;
    }
    protected function get_file_bdd_by_id($file_id){
        Capsule::disableQueryLog();
        if($this->options['imageMode'] == 'segmentation'){
            $getSet = SegImage::where('id',  $file_id)
                                ->with('set')
                                ->first();
        }else{
            $getSet = ImgLinks::where('id',  $file_id)
                                ->with('set')
                                ->first();
            //$getSet = Capsule::table('labelimglinks')->where('id',  $file_id)
                                //->with('set')
            //                    ->first();
        }
        //return 1;
        if(!$getSet) return '';
        $result = $getSet->toArray();
        return $result;
    }
    protected function get_file_size($file_path, $clear_stat_cache = false) {
        if ($clear_stat_cache) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                clearstatcache(true, $file_path);
            } else {
                clearstatcache();
            }
        }
        if(file_exists($file_path)){
            return $this->fix_integer_overflow(filesize($file_path));
        }else{
            return 0; 
        }
    }

    protected function is_valid_file_object($file_name) {
        $file_path = $this->get_upload_path($file_name);
        if (is_file($file_path) && $file_name[0] !== '.') {
            return true;
        }
        return false;
    }

    protected function get_file_object($file_name) {
        if ($this->is_valid_file_object($file_name)) {
            //check grp, if not in allowed grp, return null
            if($this->options['imageMode'] == 'segmentation'){
                $getGrp = SegImage::where('path',  $file_name)
                    ->with('group')
                    ->first();
            }else{
                $getGrp = ImgLinks::where('path',  $file_name)
                    ->with('group')
                    ->first();
            }
            $result = $getGrp->toArray();
            if($result['group'] &&  !in_array($result['group']['id'], $this->options['groups'])  )
                return null;
            ///////

            $file = new \stdClass();
            $file->name = $file_name;
            $bddFile = $this->get_file_bdd($file_name);
            $file->imgID = $bddFile['id'];
            $file->set = $bddFile['set']['name'];
            $file->setID = $bddFile['set_id'];
            $file->group = $this->get_file_group($file->setID);
            $file->size = $this->get_file_size(
                $this->get_upload_path($file_name)
            );
            $file->url = $this->get_download_url($file->name);
            foreach ($this->options['image_versions'] as $version => $options) {
                if (!empty($version)) {
                    if (is_file($this->get_upload_path($file_name, $version))) {
                        $file->{$version.'Url'} = $this->get_download_url(
                            $file->name,
                            $version
                        );
                    }
                }
            }
            $this->set_additional_file_properties($file);
            return $file;
        }
        return null;
    }
    protected function get_file_object_by_id($file_id) {
        //check grp, if not in allowed grp, return null
        if($this->options['imageMode'] == 'segmentation'){
            $getGrp = SegImage::where('id',  $file_id)
                ->with('group')
                ->first();
        }else{
            $getGrp = ImgLinks::where('id',  $file_id)
                ->with('group')
                ->first();
        }
        if(!$getGrp)return;
        $result = $getGrp->toArray();
        if($result['group'] &&  !in_array($result['group']['id'], $this->options['groups'])  )
            return null;
        ///////

        $file = new \stdClass();
        $bddFile = $this->get_file_bdd_by_id($file_id);
        $file->name = $bddFile['path'];
        $file->originalName = $bddFile['originalName'];
        if($file->originalName == ""){
            $file->originalName = "original Name not found";
        }
        $file->imgID = $bddFile['id'];
        $file->set = $bddFile['set']['name'];
        $file->setID = $bddFile['set_id'];
        $file->group = $this->get_file_group($file->setID);
        $file->size = $this->get_file_size(
            $this->get_upload_path($file->name)
        );
        $file->url = $this->get_download_url($file->name);
        foreach ($this->options['image_versions'] as $version => $options) {
            if (!empty($version)) {
                if (is_file($this->get_upload_path($file->name, $version))) {
                    $file->{$version.'Url'} = $this->get_download_url(
                        $file->name,
                        $version
                    );
                }
            }
        }
        $this->set_additional_file_properties($file);
        return $file;
    }

    protected function get_file_objects($iteration_method = 'get_file_object') {
        $upload_dir = $this->get_upload_path();
        if (!is_dir($upload_dir)) {
            return array();
        }
        return array_values(array_filter(array_map(
            array($this, $iteration_method),
            scandir($upload_dir)
        )));
    }
    protected function get_file_objects_custom($params) {
        $classMapper = $this->ci->classMapper;
        if($this->options['imageMode'] == 'segmentation'){
            $SpImg = new SegImageSprunje($classMapper, $params);
        }else{
            $SpImg = new ImgLinksSprunje($classMapper, $params);
        }
        $spResult = $SpImg->getResults();
        $getImg = $spResult["rows"];
        $result = [];
        foreach ($getImg as $img) {
            array_push($result, $img["id"]);
        }
        $getObj = array_values(array_filter(array_map(
            array($this, 'get_file_object_by_id'),
            $result
        )));
        return array("count"=>$spResult["count_filtered"],"rows"=>$getObj);
    }

    protected function count_file_objects() {
        return count($this->get_file_objects('is_valid_file_object'));
    }

    protected function get_error_message($error) {
        return isset($this->error_messages[$error]) ?
            $this->error_messages[$error] : $error;
    }

    public function get_config_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int)$val;
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $this->fix_integer_overflow($val);
    }

    protected function validate($uploaded_file, $file, $error, $index) {
        if ($error) {
            $file->error = $this->get_error_message($error);
            error_log("Invalid 1");
            return false;
        }
        $content_length = $this->fix_integer_overflow(
            (int)$this->get_server_var('CONTENT_LENGTH')
        );
        $post_max_size = $this->get_config_bytes(ini_get('post_max_size'));
        if ($post_max_size && ($content_length > $post_max_size)) {
            $file->error = $this->get_error_message('post_max_size');
            error_log("Invalid 2");
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = $this->get_error_message('accept_file_types');
            error_log("Invalid 3");
            return false;
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = $this->get_file_size($uploaded_file);
            error_log("file size  val : ".$file_size);
        } else {
            //$file_size = $content_length;
            $file_size = $this->get_file_size($uploaded_file);
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
            ) {
            $file->error = $this->get_error_message('max_file_size');
            error_log("Invalid 4");
            error_log("File size : ".$file_size);
            return false;
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            $file->error = $this->get_error_message('min_file_size');
            error_log("Invalid 5");
            return false;
        }
        if (is_int($this->options['max_number_of_files']) &&
                ($this->count_file_objects() >= $this->options['max_number_of_files']) &&
                // Ignore additional chunks of existing files:
                !is_file($this->get_upload_path($file->name))) {
            $file->error = $this->get_error_message('max_number_of_files');
            error_log("Invalid 6");
            return false;
        }
        $max_width = @$this->options['max_width'];
        $max_height = @$this->options['max_height'];
        $min_width = @$this->options['min_width'];
        $min_height = @$this->options['min_height'];
        if (($max_width || $max_height || $min_width || $min_height)
           && preg_match($this->options['image_file_types'], $file->name)) {
            list($img_width, $img_height) = $this->get_image_size($uploaded_file);

            // If we are auto rotating the image by default, do the checks on
            // the correct orientation
            if (
                @$this->options['image_versions']['']['auto_orient'] &&
                function_exists('exif_read_data') &&
                ($exif = @exif_read_data($uploaded_file)) &&
                (((int) @$exif['Orientation']) >= 5)
            ) {
                $tmp = $img_width;
                $img_width = $img_height;
                $img_height = $tmp;
                unset($tmp);
            }

        }
        if (!empty($img_width)) {
            if ($max_width && $img_width > $max_width) {
                $file->error = $this->get_error_message('max_width');
                error_log("Invalid 7");
                return false;
            }
            if ($max_height && $img_height > $max_height) {
                $file->error = $this->get_error_message('max_height');
                error_log("Invalid 8");
                return false;
            }
            if ($min_width && $img_width < $min_width) {
                $file->error = $this->get_error_message('min_width');
                error_log("Invalid 9");
                return false;
            }
            if ($min_height && $img_height < $min_height) {
                $file->error = $this->get_error_message('min_height');
                error_log("Invalid 10");
                return false;
            }
        }
        return true;
    }

    protected function upcount_name_callback($matches) {
        $index = isset($matches[1]) ? ((int)$matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return ' ('.$index.')'.$ext;
    }

    protected function upcount_name($name) {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }

    protected function get_unique_filename($file_path, $name, $size, $type, $error,
            $index, $content_range) {
        while(is_dir($this->get_upload_path($name))) {
            $name = $this->upcount_name($name);
        }
        // Keep an existing filename if this is part of a chunked upload:
        $uploaded_bytes = $this->fix_integer_overflow((int)$content_range[1]);
        while (is_file($this->get_upload_path($name))) {
            if ($uploaded_bytes === $this->get_file_size(
                    $this->get_upload_path($name))) {
                break;
            }
            $name = $this->upcount_name($name);
        }
        return $name;
    }

    protected function fix_file_extension($file_path, $name, $size, $type, $error,
            $index, $content_range) {
        // Add missing file extension for known image types:
        if (strpos($name, '.') === false &&
                preg_match('/^image\/(jpe?g|png)/', $type, $matches)) {
            $name .= '.'.$matches[1];
        }
        if ($this->options['correct_image_extensions'] &&
                function_exists('exif_imagetype')) {
            switch (@exif_imagetype($file_path)){
                case IMAGETYPE_JPEG:
                    $extensions = array('jpg', 'jpeg');
                    break;
                case IMAGETYPE_PNG:
                    $extensions = array('png');
                    break;
                case IMAGETYPE_GIF:
                    $extensions = array('gif');
                    break;
            }
            // Adjust incorrect image file extensions:
            if (!empty($extensions)) {
                $parts = explode('.', $name);
                $extIndex = count($parts) - 1;
                $ext = strtolower(@$parts[$extIndex]);
                if (!in_array($ext, $extensions)) {
                    $parts[$extIndex] = $extensions[0];
                    $name = implode('.', $parts);
                }
            }
        }
        return $name;
    }

    protected function trim_file_name($file_path, $name, $size, $type, $error,
            $index, $content_range) {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $name = trim($this->basename(stripslashes($name)), ".\x00..\x20");
        // Use a timestamp for empty filenames:
        if (!$name) {
            $name = str_replace('.', '-', microtime(true));
        }
        return $name;
    }

    protected function get_file_name($file_path, $name, $size, $type, $error,
            $index, $content_range) {
        $name = $this->trim_file_name($file_path, $name, $size, $type, $error,
            $index, $content_range);
        return $this->get_unique_filename(
            $file_path,
            $this->fix_file_extension($file_path, $name, $size, $type, $error,
                $index, $content_range),
            $size,
            $type,
            $error,
            $index,
            $content_range
        );
    }

    protected function get_scaled_image_file_paths($file_name, $version) {
        $file_path = $this->get_upload_path($file_name);
        if (!empty($version)) {
            $version_dir = $this->get_upload_path(null, $version);
            if (!is_dir($version_dir)) {
                mkdir($version_dir, $this->options['mkdir_mode'], true);
            }
            $new_file_path = $version_dir.'/'.$file_name;
        } else {
            $new_file_path = $file_path;
        }
        return array($file_path, $new_file_path);
    }

    protected function gd_get_image_object($file_path, $func, $no_cache = false) {
        ini_set ('gd.jpeg_ignore_warning', 1);
        if (empty($this->image_objects[$file_path]) || $no_cache) {
            $this->gd_destroy_image_object($file_path);
            $this->image_objects[$file_path] = $func($file_path);
        }
        return $this->image_objects[$file_path];
    }

    protected function gd_set_image_object($file_path, $image) {
        $this->gd_destroy_image_object($file_path);
        $this->image_objects[$file_path] = $image;
    }

    protected function gd_destroy_image_object($file_path) {
        $image = (isset($this->image_objects[$file_path])) ? $this->image_objects[$file_path] : null ;
        return $image && imagedestroy($image);
    }

    protected function gd_imageflip($image, $mode) {
        if (function_exists('imageflip')) {
            return imageflip($image, $mode);
        }
        $new_width = $src_width = imagesx($image);
        $new_height = $src_height = imagesy($image);
        $new_img = imagecreatetruecolor($new_width, $new_height);
        $src_x = 0;
        $src_y = 0;
        switch ($mode) {
            case '1': // flip on the horizontal axis
                $src_y = $new_height - 1;
                $src_height = -$new_height;
                break;
            case '2': // flip on the vertical axis
                $src_x  = $new_width - 1;
                $src_width = -$new_width;
                break;
            case '3': // flip on both axes
                $src_y = $new_height - 1;
                $src_height = -$new_height;
                $src_x  = $new_width - 1;
                $src_width = -$new_width;
                break;
            default:
                return $image;
        }
        imagecopyresampled(
            $new_img,
            $image,
            0,
            0,
            $src_x,
            $src_y,
            $new_width,
            $new_height,
            $src_width,
            $src_height
        );
        return $new_img;
    }

    protected function gd_orient_image($file_path, $src_img) {
        if (!function_exists('exif_read_data')) {
            return false;
        }
        $exif = @exif_read_data($file_path);
        if ($exif === false) {
            return false;
        }
        $orientation = (int)@$exif['Orientation'];
        if ($orientation < 2 || $orientation > 8) {
            return false;
        }
        switch ($orientation) {
            case 2:
                $new_img = $this->gd_imageflip(
                    $src_img,
                    defined('IMG_FLIP_VERTICAL') ? IMG_FLIP_VERTICAL : 2
                );
                break;
            case 3:
                $new_img = imagerotate($src_img, 180, 0);
                break;
            case 4:
                $new_img = $this->gd_imageflip(
                    $src_img,
                    defined('IMG_FLIP_HORIZONTAL') ? IMG_FLIP_HORIZONTAL : 1
                );
                break;
            case 5:
                $tmp_img = $this->gd_imageflip(
                    $src_img,
                    defined('IMG_FLIP_HORIZONTAL') ? IMG_FLIP_HORIZONTAL : 1
                );
                $new_img = imagerotate($tmp_img, 270, 0);
                imagedestroy($tmp_img);
                break;
            case 6:
                $new_img = imagerotate($src_img, 270, 0);
                break;
            case 7:
                $tmp_img = $this->gd_imageflip(
                    $src_img,
                    defined('IMG_FLIP_VERTICAL') ? IMG_FLIP_VERTICAL : 2
                );
                $new_img = imagerotate($tmp_img, 270, 0);
                imagedestroy($tmp_img);
                break;
            case 8:
                $new_img = imagerotate($src_img, 90, 0);
                break;
            default:
                return false;
        }
        $this->gd_set_image_object($file_path, $new_img);
        return true;
    }

    protected function gd_create_scaled_image($file_name, $version, $options) {
        if (!function_exists('imagecreatetruecolor')) {
            error_log('Function not found: imagecreatetruecolor');
            return false;
        }
        list($file_path, $new_file_path) =
            $this->get_scaled_image_file_paths($file_name, $version);
        $type = strtolower(substr(strrchr($file_name, '.'), 1));
        switch ($type) {
            case 'jpg':
            case 'jpeg':
                $src_func = 'imagecreatefromjpeg';
                $write_func = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ?
                    $options['jpeg_quality'] : 75;
                break;
            case 'gif':
                $src_func = 'imagecreatefromgif';
                $write_func = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                $src_func = 'imagecreatefrompng';
                $write_func = 'imagepng';
                $image_quality = isset($options['png_quality']) ?
                    $options['png_quality'] : 9;
                break;
            default:
                return false;
        }
        if($version == ''){return true;}
        //if($version == 'thumbnail' ){return true;}
        //if($version == 'light' ){return true;}
        //error_log($this->get_memory_usage()." before src_img");
        $src_img = $this->gd_get_image_object(
            $file_path,
            $src_func,
            !empty($options['no_cache'])
        );
        $image_oriented = false;
        if (!empty($options['auto_orient']) && $this->gd_orient_image(
                $file_path,
                $src_img
            )) {
            $image_oriented = true;
            $src_img = $this->gd_get_image_object(
                $file_path,
                $src_func
            );
        }
        //error_log(print_r($src_img,true));
        $max_width = $img_width = imagesx($src_img);
        $max_height = $img_height = imagesy($src_img);
        //error_log(print_r($max_width,true));
        //error_log(print_r($max_height,true));
        if (!empty($options['max_width'])) {
            $max_width = $options['max_width'];
        }
        if (!empty($options['max_height'])) {
            $max_height = $options['max_height'];
        }
        $scale = min(
            $max_width / $img_width,
            $max_height / $img_height
        );
        if ($scale >= 1) {
            if ($image_oriented) {
                return $write_func($src_img, $new_file_path, $image_quality);
            }
            if ($file_path !== $new_file_path) {
                return copy($file_path, $new_file_path);
            }
            return true;
        }
        
        if (empty($options['crop'])) {
            $new_width = $img_width * $scale;
            $new_height = $img_height * $scale;
            $dst_x = 0;
            $dst_y = 0;
            $new_img = imagecreatetruecolor($new_width, $new_height);
        } else {
            if (($img_width / $img_height) >= ($max_width / $max_height)) {
                $new_width = $img_width / ($img_height / $max_height);
                $new_height = $max_height;
            } else {
                $new_width = $max_width;
                $new_height = $img_height / ($img_width / $max_width);
            }
            $dst_x = 0 - ($new_width - $max_width) / 2;
            $dst_y = 0 - ($new_height - $max_height) / 2;
            $new_img = imagecreatetruecolor($max_width, $max_height);
        }
        // Handle transparency in GIF and PNG images:
        switch ($type) {
            case 'gif':
            case 'png':
                imagecolortransparent($new_img, imagecolorallocate($new_img, 0, 0, 0));
            case 'png':
                imagealphablending($new_img, false);
                imagesavealpha($new_img, true);
                break;
        }
        $success = imagecopyresampled(
            $new_img,
            $src_img,
            $dst_x,
            $dst_y,
            0,
            0,
            $new_width,
            $new_height,
            $img_width,
            $img_height
        ) && $write_func($new_img, $new_file_path, $image_quality);
        //error_log("gd end");

        //error_log(print_r($file_path,true));
        //error_log(print_r($new_img,true));
        //error_log(print_r($src_img,true));
        //error_log($this->get_memory_usage());
        $this->gd_set_image_object($file_path, $new_img);
        //error_log(memory_get_usage());
        //imagedestroy($src_img);
        //$src_img = null;
        //error_log(memory_get_usage());
        error_log($this->get_memory_usage()." scale ".$version." ".$scale." ".$src_img." ".$file_path);
        //$optionsize = strlen(serialize($this->options));
        //error_log($optionsize." option size");
        //error_log(print_r($src_img,true));
        //error_log(print_r($this->image_objects,true));
        //error_log(print_r($success,true));
        return $success;
    }

    protected function imagick_get_image_object($file_path, $no_cache = false) {
        if (empty($this->image_objects[$file_path]) || $no_cache) {
            $this->imagick_destroy_image_object($file_path);
            $image = new \Imagick();
            if (!empty($this->options['imagick_resource_limits'])) {
                foreach ($this->options['imagick_resource_limits'] as $type => $limit) {
                    $image->setResourceLimit($type, $limit);
                }
            }
            $image->readImage($file_path);
            $this->image_objects[$file_path] = $image;
        }
        return $this->image_objects[$file_path];
    }

    protected function imagick_set_image_object($file_path, $image) {
        $this->imagick_destroy_image_object($file_path);
        $this->image_objects[$file_path] = $image;
    }

    protected function imagick_destroy_image_object($file_path) {
        $image = (isset($this->image_objects[$file_path])) ? $this->image_objects[$file_path] : null ;
        return $image && $image->destroy();
    }

    protected function imagick_orient_image($image) {
        $orientation = $image->getImageOrientation();
        $background = new \ImagickPixel('none');
        switch ($orientation) {
            case \imagick::ORIENTATION_TOPRIGHT: // 2
                $image->flopImage(); // horizontal flop around y-axis
                break;
            case \imagick::ORIENTATION_BOTTOMRIGHT: // 3
                $image->rotateImage($background, 180);
                break;
            case \imagick::ORIENTATION_BOTTOMLEFT: // 4
                $image->flipImage(); // vertical flip around x-axis
                break;
            case \imagick::ORIENTATION_LEFTTOP: // 5
                $image->flopImage(); // horizontal flop around y-axis
                $image->rotateImage($background, 270);
                break;
            case \imagick::ORIENTATION_RIGHTTOP: // 6
                $image->rotateImage($background, 90);
                break;
            case \imagick::ORIENTATION_RIGHTBOTTOM: // 7
                $image->flipImage(); // vertical flip around x-axis
                $image->rotateImage($background, 270);
                break;
            case \imagick::ORIENTATION_LEFTBOTTOM: // 8
                $image->rotateImage($background, 270);
                break;
            default:
                return false;
        }
        $image->setImageOrientation(\imagick::ORIENTATION_TOPLEFT); // 1
        return true;
    }

    protected function imagick_create_scaled_image($file_name, $version, $options) {
        list($file_path, $new_file_path) =
            $this->get_scaled_image_file_paths($file_name, $version);
        $image = $this->imagick_get_image_object(
            $file_path,
            !empty($options['crop']) || !empty($options['no_cache'])
        );
        if ($image->getImageFormat() === 'GIF') {
            // Handle animated GIFs:
            $images = $image->coalesceImages();
            foreach ($images as $frame) {
                $image = $frame;
                $this->imagick_set_image_object($file_name, $image);
                break;
            }
        }
        $image_oriented = false;
        if (!empty($options['auto_orient'])) {
            $image_oriented = $this->imagick_orient_image($image);
        }
        $new_width = $max_width = $img_width = $image->getImageWidth();
        $new_height = $max_height = $img_height = $image->getImageHeight();
        if (!empty($options['max_width'])) {
            $new_width = $max_width = $options['max_width'];
        }
        if (!empty($options['max_height'])) {
            $new_height = $max_height = $options['max_height'];
        }
        if (!($image_oriented || $max_width < $img_width || $max_height < $img_height)) {
            if ($file_path !== $new_file_path) {
                return copy($file_path, $new_file_path);
            }
            return true;
        }
        $crop = !empty($options['crop']);
        if ($crop) {
            $x = 0;
            $y = 0;
            if (($img_width / $img_height) >= ($max_width / $max_height)) {
                $new_width = 0; // Enables proportional scaling based on max_height
                $x = ($img_width / ($img_height / $max_height) - $max_width) / 2;
            } else {
                $new_height = 0; // Enables proportional scaling based on max_width
                $y = ($img_height / ($img_width / $max_width) - $max_height) / 2;
            }
        }
        $success = $image->resizeImage(
            $new_width,
            $new_height,
            isset($options['filter']) ? $options['filter'] : \imagick::FILTER_LANCZOS,
            isset($options['blur']) ? $options['blur'] : 1,
            $new_width && $new_height // fit image into constraints if not to be cropped
        );
        if ($success && $crop) {
            $success = $image->cropImage(
                $max_width,
                $max_height,
                $x,
                $y
            );
            if ($success) {
                $success = $image->setImagePage($max_width, $max_height, 0, 0);
            }
        }
        $type = strtolower(substr(strrchr($file_name, '.'), 1));
        switch ($type) {
            case 'jpg':
            case 'jpeg':
                if (!empty($options['jpeg_quality'])) {
                    $image->setImageCompression(\imagick::COMPRESSION_JPEG);
                    $image->setImageCompressionQuality($options['jpeg_quality']);
                }
                break;
        }
        if (!empty($options['strip'])) {
            $image->stripImage();
        }
        return $success && $image->writeImage($new_file_path);
    }

    protected function imagemagick_create_scaled_image($file_name, $version, $options) {
        list($file_path, $new_file_path) =
            $this->get_scaled_image_file_paths($file_name, $version);
        $resize = @$options['max_width']
            .(empty($options['max_height']) ? '' : 'X'.$options['max_height']);
        if (!$resize && empty($options['auto_orient'])) {
            if ($file_path !== $new_file_path) {
                return copy($file_path, $new_file_path);
            }
            return true;
        }
        $cmd = $this->options['convert_bin'];
        if (!empty($this->options['convert_params'])) {
            $cmd .= ' '.$this->options['convert_params'];
        }
        $cmd .= ' '.escapeshellarg($file_path);
        if (!empty($options['auto_orient'])) {
            $cmd .= ' -auto-orient';
        }
        if ($resize) {
            // Handle animated GIFs:
            $cmd .= ' -coalesce';
            if (empty($options['crop'])) {
                $cmd .= ' -resize '.escapeshellarg($resize.'>');
            } else {
                $cmd .= ' -resize '.escapeshellarg($resize.'^');
                $cmd .= ' -gravity center';
                $cmd .= ' -crop '.escapeshellarg($resize.'+0+0');
            }
            // Make sure the page dimensions are correct (fixes offsets of animated GIFs):
            $cmd .= ' +repage';
        }
        if (!empty($options['convert_params'])) {
            $cmd .= ' '.$options['convert_params'];
        }
        $cmd .= ' '.escapeshellarg($new_file_path);
        exec($cmd, $output, $error);
        if ($error) {
            error_log(implode('\n', $output));
            return false;
        }
        return true;
    }

    protected function get_image_size($file_path) {
        if ($this->options['image_library']) {
            if (extension_loaded('imagick')) {
                error_log("imagick");
                $image = new \Imagick();
                try {
                    if (@$image->pingImage($file_path)) {
                        $dimensions = array($image->getImageWidth(), $image->getImageHeight());
                        $image->destroy();
                        return $dimensions;
                    }
                    return false;
                } catch (\Exception $e) {
                    error_log($e->getMessage());
                }
            }
            if ($this->options['image_library'] === 2) {
                $cmd = $this->options['identify_bin'];
                $cmd .= ' -ping '.escapeshellarg($file_path);
                exec($cmd, $output, $error);
                if (!$error && !empty($output)) {
                    // image.jpg JPEG 1920x1080 1920x1080+0+0 8-bit sRGB 465KB 0.000u 0:00.000
                    $infos = preg_split('/\s+/', substr($output[0], strlen($file_path)));
                    $dimensions = preg_split('/x/', $infos[2]);
                    return $dimensions;
                }
                return false;
            }
        }
        if (!function_exists('getimagesize')) {
            error_log('Function not found: getimagesize');
            return false;
        }
        return @getimagesize($file_path);
    }

    protected function create_scaled_image($file_name, $version, $options) {
        if ($this->options['image_library'] === 2) {
            return $this->imagemagick_create_scaled_image($file_name, $version, $options);
        }
        if ($this->options['image_library'] && extension_loaded('imagick')) {
            return $this->imagick_create_scaled_image($file_name, $version, $options);
        }
        return $this->gd_create_scaled_image($file_name, $version, $options);
    }

    protected function destroy_image_object($file_path) {
        if ($this->options['image_library'] && extension_loaded('imagick')) {
            return $this->imagick_destroy_image_object($file_path);
        }
    }

    protected function is_valid_image_file($file_path) {
        if (!preg_match($this->options['image_file_types'], $file_path)) {
            return false;
        }
        if (function_exists('exif_imagetype')) {
            return @exif_imagetype($file_path);
        }
        $image_info = $this->get_image_size($file_path);
        return $image_info && $image_info[0] && $image_info[1];
    }

    protected function handle_image_file($file_path, $file) {
        $failed_versions = array();
        foreach ($this->options['image_versions'] as $version => $options) {
            if ($this->create_scaled_image($file->name, $version, $options)) {
                if (!empty($version)) {
                    $file->{$version.'Url'} = $this->get_download_url(
                        $file->name,
                        $version
                    );
                } else {
                    $file->size = $this->get_file_size($file_path, true);
                }
            } else {
                $failed_versions[] = $version ? $version : 'original';
            }
        }
        if (count($failed_versions)) {
            $file->error = $this->get_error_message('image_resize')
                    .' ('.implode($failed_versions, ', ').')';
        }
        // Free memory:
        $this->destroy_image_object($file_path);
    }

    protected function handle_zip_upload($uploaded_file, $name, $size, $type, $error,
            $index = null, $content_range = null) {

        
        $zipFileNameStripped = pathinfo($uploaded_file,PATHINFO_FILENAME);
        $addData = $this->handle_form_data($name, $index);
        
        $tmpFileLoc = "efs/tmp/import/";
        $tmpFolder = $this->basename($name).microtime();
        $thingsToReplace = array(' ','.');
        $tmpFolder = str_replace($thingsToReplace, "_", $tmpFolder);
        mkdir($tmpFileLoc.$tmpFolder, 0700);
        $file = new \stdClass();
        $file->name = $this->get_file_name($uploaded_file, $name, $size, $type, $error,
            $index, $content_range);
        $file->originalName = $name;
        $file->size = $this->fix_integer_overflow((int)$size);
        $this->set_additional_file_properties($file);
        $za = new \ZipArchive(); 

        $za->open($uploaded_file); 
        set_time_limit(0);
        session_start();
        $_SESSION['upload_status'][$this->ci->currentUser->id][$zipFileNameStripped]["upload_current"] = 0;
        $_SESSION['upload_status'][$this->ci->currentUser->id][$zipFileNameStripped]["upload_total"] = $za->numFiles;
        session_write_close();
        $Oname = $za->getFromName('filename.txt');
        $OnamesList = [];
        if($Oname != null){
            $Onames = array_filter(explode("\n",$Oname));
            foreach ($Onames as $key => $value) {
                $namesPart = array_filter(explode(",",$value));
                $OnamesList[$namesPart[0]] = $namesPart[1];
            }
        }
        Capsule::connection()->disableQueryLog();
        /////////////////////////////////////////////////////////////////
        for( $i = 0; $i < $za->numFiles; $i++ ){ 
            $this->image_objects = array();
            $stat = $za->statIndex( $i ); 
            $file_type = $this->get_file_type($stat['name']);
            if($file_type == 'image/jpeg' || $file_type == 'image/png'){
                if($za->extractTo($tmpFileLoc.$tmpFolder, array($za->getNameIndex($i)))){
                    $filename = pathinfo($stat['name'],PATHINFO_FILENAME);
                    $areaData = $za->getFromName($filename.'.txt');
                    unset($filename);
                    $OriginalName = $OnamesList[$stat['name']];
                    $data = array("data"=>$areaData, "set"=>$addData['set'], "Oname"=>$OriginalName);
                    unset($areaData);
                    $this->handle_file_upload($tmpFileLoc.$tmpFolder.'/'.$za->getNameIndex($i), 
                                        $stat['name'], 
                                        $stat['size'], 
                                        $file_type, 
                                        $error,
                                        null,
                                        null,
                                        $data);
                    unset($data);
                }else{
                    error_log(print_r('Unable to extract the file.'));
                    error_log($za->getStatusString());
                }
                
            }
            else{
                error_log("file is not an image");
            }
            session_start();
            if(!array_key_exists($zipFileNameStripped,$_SESSION['upload_status'][$this->ci->currentUser->id])){
                $_SESSION['upload_status'][$this->ci->currentUser->id][$zipFileNameStripped] = [];
            }
            $_SESSION['upload_status'][$this->ci->currentUser->id][$zipFileNameStripped]["upload_current"] = $i+1;
            $_SESSION['upload_status'][$this->ci->currentUser->id][$zipFileNameStripped]["upload_total"] = $za->numFiles;
            session_write_close();
            
            foreach ($_FILES['files']['tmp_name'] as $key => $value) {
                //$eye = pathinfo($value,PATHINFO_FILENAME);
                //error_log($eye);
            }
            unset($stat);
            unset($file_type);
            error_log($this->get_memory_usage()." in loop");     
        }
        error_log("zip loop");
        error_log($this->get_memory_usage());
        $za->close();
        $this->rrmdir($tmpFileLoc.$tmpFolder);
        ///////////////////////////////////////////////////////////////////////
        set_time_limit(120);
        //Reset to initial state
        session_start();
        $_SESSION['upload_status'][$this->ci->currentUser->id][$zipFileNameStripped] = [];
        unset($_SESSION['upload_status'][$this->ci->currentUser->id][$zipFileNameStripped]);
        session_write_close();
        return $file;
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error,
            $index = null, $content_range = null, $data = null) {
        
        //error_log("handle size ". $size);

        $file = new \stdClass();
    //  $file->name = $this->get_file_name($uploaded_file, $name, $size, $type, $error,$index, $content_range);
	//	error_log('in before insert func '.$uploaded_file.' name '.$name );
        if($data){
            $addData = $data;
        }else
        {
            $addData = $this->handle_form_data($name, $index);
        }
        //error_log(print_r($addData,True));
        $file->areaData = $addData['data'];
        $file->setValue = $addData['set'];
        $file->originalName = $addData['Oname'];
        if($file->originalName == "")
            $file->originalName = $name;
        
        //error_log("1 file av ".memory_get_usage());
        //error_log($file->setValue);
		$tmpHashName = sha1_file($uploaded_file);
		$file->name = $this->fix_file_extension($uploaded_file, $tmpHashName, $size, $type, $error,
            $index, $content_range);
		//error_log($file->name);
        $file->size = $this->fix_integer_overflow((int)$size);
        $file->type = $type;
        
        $fileDimensionInfo = getimagesize($uploaded_file);
		$file->naturalWidth = $fileDimensionInfo[0];
        $file->naturalHeight = $fileDimensionInfo[1];
        
		if ($this->validate($uploaded_file, $file, $error, $index)) {
            
			$returnInsert = $this->insertInDB($file);
            if($returnInsert['msg'] == "OK"){
                $bddFile = $this->get_file_bdd_by_id($returnInsert["file"]->id);
                $file->imgID = $bddFile['id'];
                $file->set = $bddFile['set']['name'];
                $file->setID = $bddFile['set_id'];
                $file->group = $this->get_file_group($file->setID);
				$upload_dir = $this->get_upload_path();
				if (!is_dir($upload_dir)) {
					mkdir($upload_dir, $this->options['mkdir_mode'], true);
				}
				$file_path = $this->get_upload_path($file->name);
				$append_file = $content_range && is_file($file_path) && $file->size > $this->get_file_size($file_path);
                ////////////////////
                if($data){//archive file
                    rename($uploaded_file, $file_path);
                }else{
                    if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                        // multipart/formdata uploads (POST method uploads)
                        if ($append_file) {
                            file_put_contents(
                                $file_path,
                                fopen($uploaded_file, 'r')
                            );
                        } else {
                            move_uploaded_file($uploaded_file, $file_path);
                        }
                    } else {
                        // Non-multipart uploads (PUT method support)
                        file_put_contents(
                            $file_path,
                            fopen($this->options['input_stream'], 'r'),
                            $append_file ? FILE_APPEND : 0
                        );
                    }
                }
				
				$file_size = $this->get_file_size($file_path, $append_file);
                error_log($file_path);
                error_log($append_file);
                error_log($file_size);
                error_log($file->size);
				if ($file_size === $file->size) {
                  error_log("upload handler non abort size test");
					$file->url = $this->get_download_url($file->name);
					if ($this->is_valid_image_file($file_path)) {
						$this->handle_image_file($file_path, $file);
					}
                } else {
					$file->size = $file_size;
                  error_log("upload handler abort");
					if (!$content_range && $this->options['discard_aborted_uploads']) {
						unlink($file_path);
						$file->error = $this->get_error_message('abort');
					}
				}
				$this->set_additional_file_properties($file);
			}else{
                $error = $returnInsert['error'];
                if (strpos($error, "Duplicate entry") !== false){
					$file->error = $this->get_error_message('duplicate_key');//duplicate_key
				}else{
					$file->error = $this->get_error_message('insert_db_failed');//insert_db_failed
				}
				return $file;
			}
		}
        return $file;
		
        
    }
    
	protected function insertInDB($file){
        $filename = $file->name;
        $area = $file->areaData;
        $set = $file->setValue;
        $naturalWidth = $file->naturalWidth;
        $naturalHeight = $file->naturalHeight;
        $originalName = $file->originalName;
        if($this->options['imageMode'] == 'segmentation'){
            if ($set == '') $set = 1;
            $SegImg = new SegImage;
            $SegImg->path = $filename;
            $SegImg->set_id = $set;
            $SegImg->naturalWidth = $naturalWidth;
            $SegImg->naturalHeight = $naturalHeight;
            $SegImg->originalName = $originalName;
            
            try{
                $SegImg->save();
            }
            catch (QueryException $e){
                return array("msg"=>"NOK","error"=>$e);
            }
            
            $areas = array_filter(explode("\n",$area));

            if ($area) {
                foreach ($areas as $key => $value) {
                    $farea = explode(" ", $value);
                    $areaCategory = $farea[0];
                    $areaString = $farea[1]; 
                    //Check category
                    $checkCat = SegCategory::where('Category',  $areaCategory)
                                        ->where('set_id',$set)
                                        ->first();
                    if($checkCat){
                        $areaType = $checkCat->id;
                    } 
                    else{//Creation cat if doesn't exist
                        $catColor = $this->rand_color();
                        $CatToInsert = new SegCategories;
                        $CatToInsert->Category = $areaCategory;
                        $CatToInsert->Color = $catColor;
                        $CatToInsert->set_id = $set;
                        $CatToInsert->save();
                        $areaType = $CatToInsert->id;
                    } 
                    //insert areas
                    $areaToInsert = new SegArea;
                    $areaToInsert->source = $SegImg->id;
                    $areaToInsert->areaType = $areaType;
                    $areaToInsert->data = $areaString;
                    $areaToInsert->user = 0;
                    $areaToInsert->save();
                }
                $imgToValid = SegImage::where('id',  $SegImg->id)
                   ->first();
                $imgToValid->state = 2;
                try{
                    $imgToValid->save();
                }
                catch (QueryException $e){
                    return array("msg"=>"NOK","error"=>$e);
                }

            }
            return array("msg"=>"OK","file"=>$SegImg);
        }else{
            if ($set == '') $set = 1;
            $BboxImg = new ImgLinks;
            $BboxImg->path = $filename;
            $BboxImg->set_id = $set;
            $BboxImg->naturalWidth = $naturalWidth;
            $BboxImg->naturalHeight = $naturalHeight;
            $BboxImg->originalName = $originalName;

            try{
                $BboxImg->save();
            }
            catch (QueryException $e){
                return array("msg"=>"NOK","error"=>$e);
            }
            $areas = array_filter(explode("\n",$area));
            
            if ($area) {
                foreach ($areas as $key => $value) {
                    $farea = explode(" ", $value);
                    //Get Values
                    $rectType = $farea[0];
                    $rectLeft = $farea[4];
                    $rectTop = $farea[5];
                    $rectRight = $farea[6];
                    $rectBottom = $farea[7];
                    //Check category
                    $checkCat = ImgCategories::where('Category',  $rectType)
                                        ->where('set_id',$set)
                                        ->first();
                    if($checkCat){
                        $rectType = $checkCat->id;
                    }
                    else{//Creation cat if doesn't exist
                        $catColor = $this->rand_color();
                        $CatToInsert = new ImgCategories;
                        $CatToInsert->Category = $rectType;
                        $CatToInsert->Color = $catColor;
                        $CatToInsert->set_id = $set;
                        $CatToInsert->save();
                        $rectType = $CatToInsert->id;
                    }
                    //insert areas
                    $areaToInsert = new ImgArea;
                    $areaToInsert->source = $BboxImg->id;
                    $areaToInsert->rectType = $rectType;
                    $areaToInsert->rectLeft = $rectLeft;
                    $areaToInsert->rectTop = $rectTop;
                    $areaToInsert->rectRight = $rectRight;
                    $areaToInsert->rectBottom = $rectBottom;
                    $areaToInsert->user = 0;
                    $areaToInsert->save();
                }
                //Valid img
                $imgToValid = ImgLinks::where('id',  $BboxImg->id)
                   ->first();
                $imgToValid->state = 2;
                try{
                    $imgToValid->save();
                }
                catch (QueryException $e){
                    return array("msg"=>"NOK","error"=>$e);
                }
            }
            return array("msg"=>"OK","file"=>$BboxImg);
        }
	}
    protected function rand_color() {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }
    protected function readfile($file_path) {
        $file_size = $this->get_file_size($file_path);
        $chunk_size = $this->options['readfile_chunk_size'];
        if ($chunk_size && $file_size > $chunk_size) {
            $handle = fopen($file_path, 'rb');
            while (!feof($handle)) {
                echo fread($handle, $chunk_size);
                @ob_flush();
                @flush();
            }
            fclose($handle);
            return $file_size;
        }
        return readfile($file_path);
    }

    protected function body($str) {
        echo $str;
    }

    protected function header($str) {
        header($str);
    }

    protected function get_upload_data($id) {
        return @$_FILES[$id];
    }

    protected function get_post_param($id) {
        return @$_POST[$id];
    }

    protected function get_query_param($id) {
        return @$_GET[$id];
    }

    protected function get_server_var($id) {
        return @$_SERVER[$id];
    }

    protected function handle_form_data($file, $index) {
        // Handle form data, e.g. $_POST['description'][$index]
        $result = [];
        foreach ($_POST['name'] as $key => $value) {
            //$result[$_POST['name'][$key]]['category'] = $_POST['category'][$key];
            //$result[$_POST['name'][$key]]['group'] = $_POST['group'][$key];
            $result[$_POST['name'][$key]]['data'] = $_POST['data'][$key];
            $result[$_POST['name'][$key]]['set'] = $_POST['set'][$key];
            $result[$_POST['name'][$key]]['Oname'] = $_POST['Oname'][$key];
        }
        //error_log(print_r($_POST,true));
        //error_log(print_r($result,true));
        return $result[$file];
    }

    protected function get_version_param() {
        return $this->basename(stripslashes($this->get_query_param('version')));
    }

    protected function get_singular_param_name() {
        return substr($this->options['param_name'], 0, -1);
    }

    protected function get_file_name_param() {
        $name = $this->get_singular_param_name();
        return $this->basename(stripslashes($this->get_query_param($name)));
    }

    protected function get_file_names_params() {
        $params = $this->get_query_param($this->options['param_name']);
        if (!$params) {
            return null;
        }
        foreach ($params as $key => $value) {
            $params[$key] = $this->basename(stripslashes($value));
        }
        return $params;
    }

    protected function get_file_type($file_path) {
        switch (strtolower(pathinfo($file_path, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            default:
                return '';
        }
    }

    protected function download() {
        switch ($this->options['download_via_php']) {
            case 1:
                $redirect_header = null;
                break;
            case 2:
                $redirect_header = 'X-Sendfile';
                break;
            case 3:
                $redirect_header = 'X-Accel-Redirect';
                break;
            default:
                return $this->header('HTTP/1.1 403 Forbidden');
        }
        $file_name = $this->get_file_name_param();
        if (!$this->is_valid_file_object($file_name)) {
            return $this->header('HTTP/1.1 404 Not Found');
        }
        if ($redirect_header) {
            return $this->header(
                $redirect_header.': '.$this->get_download_url(
                    $file_name,
                    $this->get_version_param(),
                    true
                )
            );
        }
        $file_path = $this->get_upload_path($file_name, $this->get_version_param());
        // Prevent browsers from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
        if (!preg_match($this->options['inline_file_types'], $file_name)) {
            $this->header('Content-Type: application/octet-stream');
            $this->header('Content-Disposition: attachment; filename="'.$file_name.'"');
        } else {
            $this->header('Content-Type: '.$this->get_file_type($file_path));
            $this->header('Content-Disposition: inline; filename="'.$file_name.'"');
        }
        $this->header('Content-Length: '.$this->get_file_size($file_path));
        $this->header('Last-Modified: '.gmdate('D, d M Y H:i:s T', filemtime($file_path)));
        $this->readfile($file_path);
    }

    protected function send_content_type_header() {
        $this->header('Vary: Accept');
        if (strpos($this->get_server_var('HTTP_ACCEPT'), 'application/json') !== false) {
            $this->header('Content-type: application/json');
        } else {
            $this->header('Content-type: text/plain');
        }
    }

    protected function send_access_control_headers() {
        $this->header('Access-Control-Allow-Origin: '.$this->options['access_control_allow_origin']);
        $this->header('Access-Control-Allow-Credentials: '
            .($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
        $this->header('Access-Control-Allow-Methods: '
            .implode(', ', $this->options['access_control_allow_methods']));
        $this->header('Access-Control-Allow-Headers: '
            .implode(', ', $this->options['access_control_allow_headers']));
    }

    public function generate_response($content, $print_response = true) {
        $this->response = $content;
        if ($print_response) {
            $json = json_encode($content);
            $redirect = stripslashes($this->get_post_param('redirect'));
            if ($redirect && preg_match($this->options['redirect_allow_target'], $redirect)) {
                $this->header('Location: '.sprintf($redirect, rawurlencode($json)));
                return;
            }
            $this->head();
            if ($this->get_server_var('HTTP_CONTENT_RANGE')) {
                $files = isset($content[$this->options['param_name']]) ?
                    $content[$this->options['param_name']] : null;
                if ($files && is_array($files) && is_object($files[0]) && $files[0]->size) {
                    $this->header('Range: 0-'.(
                        $this->fix_integer_overflow((int)$files[0]->size) - 1
                    ));
                }
            }
            $this->body($json);
        }
        return $content;
    }

    public function get_response () {
        return $this->response;
    }

    public function head() {
        $this->header('Pragma: no-cache');
        $this->header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->header('Content-Disposition: inline; filename="files.json"');
        // Prevent Internet Explorer from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
        if ($this->options['access_control_allow_origin']) {
            $this->send_access_control_headers();
        }
        $this->send_content_type_header();
    }

    public function get($print_response = true) {
       $params["sprunjeParam"] = $this->get_query_param('sprunjeParam');
        if ($print_response && $this->get_query_param('download')) {
            return $this->download();
        }
        $file_name = $this->get_file_name_param();
        if ($file_name) {
            $response = array(
                $this->get_singular_param_name() => $this->get_file_object($file_name)
            );
        } else {
            $res = $this->get_file_objects_custom($params["sprunjeParam"]);
            $response = array(
                $this->options['param_name'] => $res["rows"],
                "count"=>$res["count"]
            );
        }
        return $this->generate_response($response, $print_response);
    }

    public function post($print_response = true) {
        ini_set('post_max_size', '0');
        session_start();
        $_SESSION['upload_status'][$this->ci->currentUser->id] = [];
        session_write_close();
        error_log("In post func");
        if ($this->get_query_param('_method') === 'DELETE') {
            return $this->delete($print_response);
        }
        $upload = $this->get_upload_data($this->options['param_name']);
        error_log("upload created");
        //error_log($upload);
        error_log(print_r($upload, true));
        // Parse the Content-Disposition header, if available:
        $content_disposition_header = $this->get_server_var('HTTP_CONTENT_DISPOSITION');
        $file_name = $content_disposition_header ?
            rawurldecode(preg_replace(
                '/(^[^"]+")|("$)/',
                '',
                $content_disposition_header
            )) : null;
        // Parse the Content-Range header, which has the following form:
        // Content-Range: bytes 0-524287/2000000
        $content_range_header = $this->get_server_var('HTTP_CONTENT_RANGE');
        $content_range = $content_range_header ?
            preg_split('/[^0-9]+/', $content_range_header) : null;
        $size =  $content_range ? $content_range[3] : null;
        $files = array();
        error_log($this->get_memory_usage()." Start");
        if ($upload) {
            //error_log("Upload_123");
            //error_log( print_r($upload, TRUE) );
            if (is_array($upload['tmp_name'])) {
                // param_name is an array identifier like "files[]",
                // $upload is a multi-dimensional array:
                foreach ($upload['tmp_name'] as $index => $value) {
                    $shorterName = $file_name ? $file_name : $upload['name'][$index];
                    error_log($shorterName);
                    if(strtolower(pathinfo($shorterName, PATHINFO_EXTENSION)) == 'zip'){
                        $before1 = ini_get('post_max_size');
                        ini_set('post_max_size', '0');
                        $before2 = ini_get('upload_max_filesize');
                        ini_set('upload_max_filesize', '0');
                        error_log("zip file found files[]");
                        $files[] = $this->handle_zip_upload(
                            $upload['tmp_name'][$index],
                            $file_name ? $file_name : $upload['name'][$index],
                            $size ? $size : $upload['size'][$index],
                            $upload['type'][$index],
                            $upload['error'][$index],
                            $index,
                            $content_range
                        );
                        ini_set('post_max_size', $before1);
                        ini_set('upload_max_filesize', $before2);
                    }
                    else{
                        $files[] = $this->handle_file_upload(
                            $upload['tmp_name'][$index],
                            $file_name ? $file_name : $upload['name'][$index],
                            $size ? $size : $upload['size'][$index],
                            $upload['type'][$index],
                            $upload['error'][$index],
                            $index,
                            $content_range
                        );  
                    }
                    
                }
            } else {
                // param_name is a single object identifier like "file",
                // $upload is a one-dimensional array:
                $shorterName = $file_name ? $file_name : (isset($upload['name']) ?
                                $upload['name'] : null);
                if(strtolower(pathinfo($shorterName, PATHINFO_EXTENSION)) == 'zip'){
                    error_log("zip file found");
                    $files[] = $this->handle_zip_upload(
                        isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                        $file_name ? $file_name : (isset($upload['name']) ?
                                $upload['name'] : null),
                        $size ? $size : (isset($upload['size']) ?
                                $upload['size'] : $this->get_server_var('CONTENT_LENGTH')),
                        isset($upload['type']) ?
                                $upload['type'] : $this->get_server_var('CONTENT_TYPE'),
                        isset($upload['error']) ? $upload['error'] : null,
                        null,
                        $content_range
                    );
                }
                else{
                    $files[] = $this->handle_file_upload(
                        isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                        $file_name ? $file_name : (isset($upload['name']) ?
                                $upload['name'] : null),
                        $size ? $size : (isset($upload['size']) ?
                                $upload['size'] : $this->get_server_var('CONTENT_LENGTH')),
                        isset($upload['type']) ?
                                $upload['type'] : $this->get_server_var('CONTENT_TYPE'),
                        isset($upload['error']) ? $upload['error'] : null,
                        null,
                        $content_range
                    );    
                }
                
            }
            //error_log(memory_get_usage());
            error_log($this->get_memory_usage()." Only once");
            $this->image_objects = array();
            error_log($this->get_memory_usage()." Only once");
        }
        $response = array($this->options['param_name'] => $files);
        error_log($this->get_memory_usage()." End");
        return $this->generate_response($response, $print_response);
    }

    public function delete($print_response = true) {
        error_log("passage dans le delete");
        $file_ids = $this->get_file_names_params();
        if (empty($file_ids)) {
            $file_ids = array($this->get_file_name_param());
        }
        $response = array();
        error_log(print_r($file_ids,true));
        foreach ($file_ids as $file_id) {
            $file_path = $this->get_upload_path_custom($file_id);
            error_log(print_r($file_path,true));
            $this->deleteInDB($file_id);
            $imgcheck = $this->get_file_bdd($file_path);
            if(!$imgcheck){
                $file = $this->get_upload_path($file_path);
                if (is_file($file)) {
                    unlink($file);
                }
                foreach ($this->options['image_versions'] as $version => $options) {
                    if (!empty($version)) {
                        $file = $this->get_upload_path($file_path, $version);
                        if (is_file($file)) {
                            unlink($file);
                        }
                    }
                }
            }
            $response[$file_id] = True;
        }
        return $this->generate_response($response, $print_response);
    }
	protected function deleteInDB($fileid){
        error_log(print_r($fileid,true));
        if($this->options['imageMode'] == 'segmentation'){
            //TODO delete table segImage
            $imgToDel = SegImage::where('id',  $fileid)
                   ->first();
            if(!$imgToDel)return;      
            $areaToDel = SegArea::where('source',  $imgToDel->id)
                   ->get();
            foreach ($areaToDel as $area) {
                $area->delete();
            }
            $imgToDel->delete();

        }else{
            $imgToDel = ImgLinks::where('id',  $fileid)
                   ->first();
            if(!$imgToDel)return;
            $areaToDel = ImgArea::where('source',  $imgToDel->id)
                   ->get();
            foreach ($areaToDel as $area) {
                $area->delete();
            }
            $imgToDel->delete();
        }
    	
	}

    protected function basename($filepath, $suffix = null) {
        $splited = preg_split('/\//', rtrim ($filepath, '/ '));
        return substr(basename('X'.$splited[count($splited)-1], $suffix), 1);
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

    protected function get_memory_usage(){
        $mem = memory_get_usage();
        $strMem = (string)$mem;
        if(strlen($strMem) == 8){$strMem = "0".$strMem;}
        if(strlen($strMem) == 10){$strMem = "00".$strMem;}
        if(strlen($strMem) == 11){$strMem = "0".$strMem;}
        $formatMem = wordwrap($strMem , 3 , ' ' , true );
        return $formatMem;
    }
}


