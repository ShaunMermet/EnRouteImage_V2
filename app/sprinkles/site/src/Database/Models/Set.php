<?php
/**
 * labelimage ()
 *
 * @link      
 * @copyright 
 * @license   
 */
namespace UserFrosting\Sprinkle\Site\Database\Models;


use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Verification Class
 *
 * Represents a set that an image belongs to.
 * @author Shaun Mermet ()
 * @property string name
 * @property string group_id
 */
class set extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "sets";

    protected $fillable = [
        "name",
        "group_id"
    ];


    /**
     * Get the group where the set belongs.
     */
    public function group()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Database\Models\Group');
    }

    /**
     * Get the dl token of the set.
     */
    public function token()
    {
        return $this->hasOne('UserFrosting\Sprinkle\Site\Database\Models\Token');
    }
}
