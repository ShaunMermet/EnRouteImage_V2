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
class token extends UFModel
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "labelimgexportlinks";

    protected $fillable = [
        "segset_id",
        "token",
        "archivePath",
        "expires",
    ];

}
