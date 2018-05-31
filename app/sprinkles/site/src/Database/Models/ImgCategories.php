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
 * Represents a category that photo can be labeled with.
 * @author Shaun Mermet ()
 * @property string Category
 * @property string Color
 */
class ImgCategories extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "labelimgcategories";

    protected $fillable = [
        "Category",
        "Color"
    ];


    /**
     * Get the group where the set belongs.
     */
    public function set()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Database\Models\Set')->with('group');
    }
}
