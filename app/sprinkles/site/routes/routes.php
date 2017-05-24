<?php
/**
 * 
 *
 * @link      
 * @copyright 
 * @license   
 */

/**
 * Routes for site management.
 */

//Pages



$app->group('/bbox', function () {
	$this->get('', 'UserFrosting\Sprinkle\Site\Controller\SiteController:pageLabel');

	$this->post('/annotate', 'UserFrosting\Sprinkle\Site\Controller\AreaController:saveAreas');

	$this->post('/annotateNA', 'UserFrosting\Sprinkle\Site\Controller\AreaController:saveAreasNoAuth');

	$this->get('/validate', 'UserFrosting\Sprinkle\Site\Controller\SiteController:pageValidate');

	$this->put('/validate/evaluate', 'UserFrosting\Sprinkle\Site\Controller\AreaController:areaEvaluate');

});//->add('authGuard');

$app->group('/segment', function () {
	$this->get('', 'UserFrosting\Sprinkle\Site\Controller\SiteController:pageSegLabel');

	$this->post('/annotate', 'UserFrosting\Sprinkle\Site\Controller\AreaController:saveSegAreas');

	$this->get('/validate', 'UserFrosting\Sprinkle\Site\Controller\SiteController:pageSegValidate');

	$this->put('/validate/evaluate', 'UserFrosting\Sprinkle\Site\Controller\AreaController:segAreaEvaluate');

});//->add('authGuard');

$app->group('/admin', function () {
	$this->get('/overview', 'UserFrosting\Sprinkle\Site\Controller\SiteController:pageValidated');

	//CLASSIC
	$this->get('/upload', 'UserFrosting\Sprinkle\Site\Controller\SiteController:pageUpload');

	$this->put('/upload/catedit', 'UserFrosting\Sprinkle\Site\Controller\CategoryController:editCategory');

	$this->get('/upload/upload', 'UserFrosting\Sprinkle\Site\Controller\SiteController:uploadHandler');

	$this->post('/upload/upload', 'UserFrosting\Sprinkle\Site\Controller\SiteController:uploadHandler');

	$this->delete('/upload/upload', 'UserFrosting\Sprinkle\Site\Controller\SiteController:uploadHandler');

	//SEGMENTATION
	$this->get('/segUpload', 'UserFrosting\Sprinkle\Site\Controller\SiteController:pageSegUpload');

	$this->put('/segUpload/catedit', 'UserFrosting\Sprinkle\Site\Controller\CategoryController:editSegCategory');

	$this->get('/segUpload/upload', 'UserFrosting\Sprinkle\Site\Controller\SiteController:segUploadHandler');

	$this->post('/segUpload/upload', 'UserFrosting\Sprinkle\Site\Controller\SiteController:segUploadHandler');

	$this->delete('/segUpload/upload', 'UserFrosting\Sprinkle\Site\Controller\SiteController:segUploadHandler');

});//->add('authGuard');

//Data
//GET

$app->group('/export', function () {
	$this->post('', 'UserFrosting\Sprinkle\Site\Controller\SiteController:prepareZip');

	$this->get('/dl/{dl_id}', 'UserFrosting\Sprinkle\Site\Controller\SiteController:returnDownload');

});//->add('authGuard');

$app->group('/segExport', function () {
	$this->post('', 'UserFrosting\Sprinkle\Site\Controller\SiteController:prepareSegZip');
});//->add('authGuard');

$app->group('/category', function () {
	$this->get('/all2', 'UserFrosting\Sprinkle\Site\Controller\CategoryController:getAllCategory2');

	$this->get('/allNA', 'UserFrosting\Sprinkle\Site\Controller\CategoryController:getAllCategoryNoAuth');

});//->add('authGuard');

$app->group('/segCategory', function () {
	$this->get('/all', 'UserFrosting\Sprinkle\Site\Controller\CategoryController:getAllSegCategory');

});//->add('authGuard');

$app->group('/images', function () {

    $this->get('/clean', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getImagesC');

    $this->get('/cleanNA', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getImagesCNoAuth');

    $this->get('/annotated', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getImagesA');

    $this->get('/myedit', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getImagesAbyMe');

    $this->get('/myeditCount', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getCountImagesAbyMe');

    //$this->get('/validated', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getImagesV');

 	$this->put('/nbrBYcategory', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getNbrImagesByCat');

 	$this->get('/imgSprunje', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getImageSprunje');

});//->add('authGuard');

$app->group('/segImages', function () {

    $this->get('/clean', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getSegImagesC');

    $this->get('/annotated', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getSegImagesA');

    $this->get('/myedit', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getSegImagesAbyMe');

    $this->get('/myeditCount', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getCountSegImagesAbyMe');

    //$this->get('/validated', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getImagesV');

 	$this->get('/nbrBYcategory', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getNbrSegImagesByCat');

 	$this->get('/imgSprunje', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getSegImageSprunje');

});//->add('authGuard');

$app->group('/areas', function () {
	$this->get('/all', 'UserFrosting\Sprinkle\Site\Controller\AreaController:getAllAreas');

	$this->get('/byIds', 'UserFrosting\Sprinkle\Site\Controller\AreaController:getAreasByIds');

	$this->get('/areaSprunje', 'UserFrosting\Sprinkle\Site\Controller\AreaController:getAreaSprunje');

	$this->get('/areauserstats', 'UserFrosting\Sprinkle\Site\Controller\AreaController:getAreaUserStats');

});//->add('authGuard');

$app->group('/segAreas', function () {
	//$this->get('/all', 'UserFrosting\Sprinkle\Site\Controller\AreaController:getAllSegAreas');

	$this->get('/byIds', 'UserFrosting\Sprinkle\Site\Controller\AreaController:getSegAreasByIds');

	$this->get('/areaSprunje', 'UserFrosting\Sprinkle\Site\Controller\AreaController:getSegAreaSprunje');

	//$this->get('/areauserstats', 'UserFrosting\Sprinkle\Site\Controller\AreaController:getAreaUserStats');

});//->add('authGuard');


//PUT
$app->group('/freeimage', function () {
	$this->put('/{img_id}', 'UserFrosting\Sprinkle\Site\Controller\ImageController:freeImage');

});//->add('authGuard');
$app->group('/freeimageNA', function () {
	$this->put('/{img_id}', 'UserFrosting\Sprinkle\Site\Controller\ImageController:freeImageNoAuth');

});//->add('authGuard');

//Overrides

$app->group('/account', function () {
    $this->post('/register', 'UserFrosting\Sprinkle\Site\Controller\Overrides\AccountController:register');
});
$app->group('/api/users', function () {
    //$this->delete('/u/{user_name}', 'UserFrosting\Sprinkle\Site\Controller\Overrides\UserController:delete');

    $this->get('', 'UserFrosting\Sprinkle\Site\Controller\Overrides\UserController:getList');

    //$this->get('/u/{user_name}', 'UserFrosting\Sprinkle\Site\Controller\Overrides\UserController:getInfo');

    //$this->get('/u/{user_name}/activities', 'UserFrosting\Sprinkle\Site\Controller\Overrides\UserController:getActivities');

    //$this->get('/u/{user_name}/roles', 'UserFrosting\Sprinkle\Site\Controller\Overrides\UserController:getRoles');

    $this->post('', 'UserFrosting\Sprinkle\Site\Controller\Overrides\UserController:create');

    //$this->post('/u/{user_name}/password-reset', 'UserFrosting\Sprinkle\Site\Controller\Overrides\UserController:createPasswordReset');

    $this->put('/u/{user_name}', 'UserFrosting\Sprinkle\Site\Controller\Overrides\UserController:updateInfo');

    //$this->put('/u/{user_name}/{field}', 'UserFrosting\Sprinkle\Site\Controller\Overrides\UserController:updateField');
})->add('authGuard');

$app->group('/modals/users', function () {
    $this->get('/edit', 'UserFrosting\Sprinkle\Site\Controller\Overrides\UserController:getModalEdit');
})->add('authGuard');

//$app->group('/group', function () {
//	$this->get('/all', 'UserFrosting\Sprinkle\Site\Controller\Overrides\GroupController:getAllGroup');

//});//->add('authGuard');