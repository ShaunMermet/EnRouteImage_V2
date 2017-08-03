<?php
/**
 * labelimage ()
 *
 * @link      
 * @copyright 
 * @license   
 */
namespace UserFrosting\Sprinkle\Site\Model;


use UserFrosting\Sprinkle\Core\Model\UFModel;

/**
 * Verification Class
 *
 * Represents a set that an image belongs to.
 * @author Shaun Mermet ()
 * @property string name
 * @property string group_id
 */
class SegSet extends UFModel
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "segsets";

    protected $fillable = [
        "name",
        "group_id"
    ];


    /**
     * Get the group where the set belongs.
     */
    public function group()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Model\Group');
    }
}
