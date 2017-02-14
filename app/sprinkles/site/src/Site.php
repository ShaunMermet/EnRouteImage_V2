<?php
/**
 * 
 *
 * @link      
 * @copyright 
 * @license   
 */

namespace UserFrosting\Sprinkle\Site;

use UserFrosting\Sprinkle\Site\ServicesProvider\SiteServicesProvider;
use UserFrosting\Sprinkle\Core\Initialize\Sprinkle;

class Site extends Sprinkle
{
    /**
     * Register Account services.
     */
    public function init()
    {
        $serviceProvider = new SiteServicesProvider();
        $serviceProvider->register($this->ci);
    }
}
