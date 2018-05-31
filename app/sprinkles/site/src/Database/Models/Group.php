<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Site\Database\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Group Class
 *
 * Represents a group object as stored in the database.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/tutorials/lesson-3-data-model/
 *
 * @property string slug
 * @property string name
 * @property string description
 * @property string icon
 */
class Group extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "groups";

    protected $fillable = [
        "slug",
        "name",
        "description",
        "icon",
        "bb_cprs_rate",
        "seg_cprs_rate"
    ];

    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = true;

    /**
     * Delete this group from the database, along with any user associations
     *
     * @todo What do we do with users when their group is deleted?  Reassign them?  Or, can a user be "groupless"?
     */
    public function delete()
    {
        // Remove all user associations
        $this->users()->detach();
        
        // Delete the group
        $result = parent::delete();

        return $result;
    }

    /**
     * Query scope to get all groupss assigned to a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->join('user_groupss', function ($join) use ($userId) {
            $join->on('user_groupss.group_id', 'groups.id')
                 ->where('user_id', $userId);
        });
    }

    /**
     * Get a list of users who have this group.
     */
    public function users()
    {
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsToMany($classMapper->getClassMapping('user'), 'user_groups', 'group_id', 'user_id');
    }



    /**
     * Lazily load a collection of Users which belong to this group.
     */
    //public function users()
    //{
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
    //    $classMapper = static::$ci->classMapper;

    //    return $this->hasMany($classMapper->getClassMapping('user'), 'group_id');
    //}
}
