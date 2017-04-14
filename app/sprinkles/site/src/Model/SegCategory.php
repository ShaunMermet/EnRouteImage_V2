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
 * Represents a category that segmentation images can be labeled with.
 * @author Shaun Mermet ()
 * @property string Category
 * @property string Color
 */
class SegCategory extends UFModel
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "segcategories";

    protected $fillable = [
        "Category",
        "Color"
    ];


    
}
