<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Model\UFModel;

/**
 * Activity Class
 *
 * Represents a single user activity at a specified point in time.
 * @author Alex Weissman (https://alexanderweissman.com)
 * @property string ip_address
 * @property int user_id
 * @property string type
 * @property datetime occurred_at
 * @property string description
 */
class Activity extends UFModel
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "activities";

    protected $fillable = [
        "ip_address",
        "user_id",
        "type",
        "occurred_at",
        "description"
    ];

    /**
     * Joins the activity's user, so we can do things like sort, search, paginate, etc.
     */
    public function scopeJoinUser($query)
    {
        $query = $query->select('activities.*');

        $query = $query->leftJoin('users', 'activities.user_id', '=', 'users.id');

        return $query;
    }

    /**
     * Add clauses to select the most recent event of each type for each user, to the query.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeMostRecentEvents($query)
    {
        return $query->select('user_id', 'event_type', Capsule::raw('MAX(occurred_at) as occurred_at'))
            ->groupBy('user_id')
            ->groupBy('type');
    }

    /**
     * Add clauses to select the most recent event of a given type for each user, to the query.
     *
     * @param string $type The type of event, matching the `event_type` field in the user_event table.
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeMostRecentEventsByType($query, $type)
    {
        return $query->select('user_id', Capsule::raw('MAX(occurred_at) as occurred_at'))
            ->where('type', $type)
            ->groupBy('user_id');
    }

    /**
     * Get the user associated with this activity.
     */
    public function user()
    {
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsTo($classMapper->getClassMapping('user'), 'user_id');
    }
}
