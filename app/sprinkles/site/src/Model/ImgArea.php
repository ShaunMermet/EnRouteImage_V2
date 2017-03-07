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
 * Represents an area drawn on an image imgLink.
 * @author Shaun Mermet ()
 * @property string source
 * @property string rectType
 * @property string rectLeft
 * @property string rectTop
 * @property string rectRight
 * @property string rectBottom
 * @property string user
 * @property string alive
 */
class ImgArea extends UFModel
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "labelimgarea";

    protected $fillable = [
        "source",
        "rectType",
        "rectLeft",
        "rectTop",
        "rectRight",
        "rectBottom",
        "user",
        "alive"
    ];


    /**
     * Get the image that owns the area.
     */
    public function imglinks()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Model\ImgLinks', 'source');
    }

    /**
     * Joins the img related to directly, so we can do things like sort, search, paginate, etc.
     */
    public function scopeJoinImglinks($query)
    {
        $query = $query->select('labelimgarea.*');

        $query = $query->leftJoin('labelimglinks', 'labelimglinks.id', '=', 'labelimgarea.source');

        return $query;
    }
    
}
