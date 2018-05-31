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
 * Represents an img ref in database.
 * @author Shaun Mermet ()
 * @property string path
 * @property string validated
 * @property int available
 * @property string requested
 * @property int Category
 */
class ImgLinks extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "labelimglinks";

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
        return $this->hasMany('UserFrosting\Sprinkle\Site\Database\Models\ImgArea','source');
    }

    /**
     * Get the group where the set belongs.
     */
    public function set()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Database\Models\Set');
    }

    /**
     * Get the group where the image belongs.
     */
    public function group()
    {
        return $this->set->belongsTo('UserFrosting\Sprinkle\Site\Database\Models\Group');
    }
}
