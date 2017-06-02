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
 * RoleSprunje
 *
 * Implements Sprunje for the roles API.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class RoleSprunje extends Sprunje
{
    protected $name = 'roles';

    protected $sortable = [
        'name',
        'description'
    ];

    protected $filterable = [
        'name',
        'description',
        'info'
    ];

    /**
     * {@inheritDoc}
     */
    protected function baseQuery()
    {
        $query = $this->classMapper->createInstance('role');

        return $query;
    }

    /**
     * Filter LIKE name OR description.
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterInfo($query, $value)
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        return $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $query = $query->orLike('name', $value)
                                ->orLike('description', $value);
            }
            return $query;
        });
    }
}
