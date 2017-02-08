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
 * Represents a category that photo can be labeled with.
 * @author Shaun Mermet ()
 * @property string Category
 * @property string Color
 */
class Category extends UFModel
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "labelimgcategories";

    protected $fillable = [
        "Category",
        "Color"
    ];


    
}
