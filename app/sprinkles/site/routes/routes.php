<?php



$app->get('/label','UserFrosting\Sprinkle\Site\Controller\SiteController:pageLabel');

$app->get('/upload','UserFrosting\Sprinkle\Site\Controller\SiteController:pageUpload');

$app->get('/validate','UserFrosting\Sprinkle\Site\Controller\SiteController:pageValidate');