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
 * Represents an segimg ref in database.
 * @author Shaun Mermet ()
 * @property string path
 * @property string validated
 * @property string requested
 * @property int Category
 */
class SegImage extends Model
{
    public $timestamps = true;

    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "SegImages";

    protected $fillable = [
        "path",
        "validated",
        "available",
        "requested",
        "category",
        "set_id"
    ];


    /**
     * Get all of the areas of the image.
     */
    public function areas()
    {
        return $this->hasMany('UserFrosting\Sprinkle\Site\Database\Models\SegArea','source');
    }
    
    /**
     * Get the group where the image belongs.
     */
    public function group()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Database\Models\Group','group');
    }
    /**
     * Get the group where the set belongs.
     */
    public function set()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Database\Models\SegSet');
    }
}
