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
 * Represents an img ref in database.
 * @author Shaun Mermet ()
 * @property string path
 * @property string validated
 * @property int available
 * @property string requested
 * @property int Category
 */
class ImgLinks extends UFModel
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
        "category"
    ];


    
}
