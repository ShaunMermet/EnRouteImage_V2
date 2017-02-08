<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Admin\Sprunje;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

/**
 * GroupSprunje
 *
 * Implements Sprunje for the groups API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class GroupSprunje extends Sprunje
{
    protected $name = 'groups';

    protected $sortable = [];

    protected $filterable = [];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        $query = $this->classMapper->createInstance('group');

        return $query;
    }
}
