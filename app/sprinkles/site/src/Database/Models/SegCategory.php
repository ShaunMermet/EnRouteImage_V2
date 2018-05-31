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
 * Represents a category that segmentation images can be labeled with.
 * @author Shaun Mermet ()
 * @property string Category
 * @property string Color
 */
class SegCategory extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "segcategories";

    protected $fillable = [
        "Category",
        "Color"
    ];


    /**
     * Get the group where the set belongs.
     */
    public function set()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Database\Models\SegSet')->with('group');
    }
}
