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
class token extends Model
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
