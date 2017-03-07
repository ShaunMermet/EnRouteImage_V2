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


$app->group('/validate', function () {
	$this->get('', 'UserFrosting\Sprinkle\Site\Controller\SiteController:pageValidate');

	$this->put('/evaluate', 'UserFrosting\Sprinkle\Site\Controller\AreaController:areaEvaluate');

});//->add('authGuard');

$app->group('/label', function () {
	$this->get('', 'UserFrosting\Sprinkle\Site\Controller\SiteController:pageLabel');

	$this->post('/annotate', 'UserFrosting\Sprinkle\Site\Controller\AreaController:saveAreas');

	$this->post('/annotateNA', 'UserFrosting\Sprinkle\Site\Controller\AreaController:saveAreasNoAuth');

});//->add('authGuard');

$app->group('/admin', function () {
	$this->get('/overview', 'UserFrosting\Sprinkle\Site\Controller\SiteController:pageValidated');

	$this->get('/upload', 'UserFrosting\Sprinkle\Site\Controller\SiteController:pageUpload');

	$this->put('/upload/catedit', 'UserFrosting\Sprinkle\Site\Controller\CategoryController:editCategory');

	$this->get('/upload/upload', 'UserFrosting\Sprinkle\Site\Controller\SiteController:uploadHandler');

	$this->post('/upload/upload', 'UserFrosting\Sprinkle\Site\Controller\SiteController:uploadHandler');

	$this->delete('/upload/upload', 'UserFrosting\Sprinkle\Site\Controller\SiteController:uploadHandler');

});//->add('authGuard');

//Data
//GET

$app->group('/export', function () {
	$this->post('', 'UserFrosting\Sprinkle\Site\Controller\SiteController:prepareZip');

	$this->get('/dl/{dl_id}', 'UserFrosting\Sprinkle\Site\Controller\SiteController:returnDownload');

});//->add('authGuard');

$app->group('/category', function () {
	$this->get('/all', 'UserFrosting\Sprinkle\Site\Controller\CategoryController:getAllCategory');

	$this->get('/all2', 'UserFrosting\Sprinkle\Site\Controller\CategoryController:getAllCategory2');

	$this->get('/allNA', 'UserFrosting\Sprinkle\Site\Controller\CategoryController:getAllCategoryNoAuth');

});//->add('authGuard');

$app->group('/images', function () {

    $this->get('/clean', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getImagesC');

    $this->get('/cleanNA', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getImagesCNoAuth');

    $this->get('/annotated', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getImagesA');

    $this->get('/validated', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getImagesV');

 	$this->put('/nbrBYcategory', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getNbrImagesByCat');

 	$this->get('/imgSprunje', 'UserFrosting\Sprinkle\Site\Controller\ImageController:getImageSprunje');

});//->add('authGuard');

$app->group('/areas', function () {
	$this->get('/all', 'UserFrosting\Sprinkle\Site\Controller\AreaController:getAllAreas');

	$this->get('/areaSprunje', 'UserFrosting\Sprinkle\Site\Controller\AreaController:getAreaSprunje');

	$this->get('/areauserstats', 'UserFrosting\Sprinkle\Site\Controller\AreaController:getAreaUserStats');

});//->add('authGuard');

//PUT
$app->group('/freeimage', function () {
	$this->put('/{img_id}', 'UserFrosting\Sprinkle\Site\Controller\ImageController:freeImage');

});//->add('authGuard');
$app->group('/freeimageNA', function () {
	$this->put('/{img_id}', 'UserFrosting\Sprinkle\Site\Controller\ImageController:freeImageNoAuth');

});//->add('authGuard');