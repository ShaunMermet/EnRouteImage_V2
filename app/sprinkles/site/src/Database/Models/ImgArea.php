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
use Illuminate\Database\Eloquent\SoftDeletes;

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
 */
class ImgArea extends Model
{

    use SoftDeletes;


    protected $dates = ['deleted_at'];
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
        "user"
    ];


    /**
     * Get the image that owns the area.
     */
    public function imglinks()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Database\Models\ImgLinks', 'source');
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
    
    /**
     * Get the category associated with the area.
     */
    public function category()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Database\Models\ImgCategories','rectType');
    }
}
