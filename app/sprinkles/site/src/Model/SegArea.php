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
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Verification Class
 *
 * Represents an area drawn on an image imgLink.
 * @author Shaun Mermet ()
 * @property string source
 * @property string areaType
 * @property string data
 * @property string user
 */
class SegArea extends UFModel
{

    use SoftDeletes;


    protected $dates = ['deleted_at'];
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "segareas";

    protected $fillable = [
        "source",
        "areaType",
        "data",
        "user"
    ];


    /**
     * Get the image that owns the area.
     */
    public function segImage()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Model\SegImage', 'source');
    }

    /**
     * Joins the img related to directly, so we can do things like sort, search, paginate, etc.
     */
    public function scopeJoinSegImage($query)
    {
        $query = $query->select('segareas.*');

        $query = $query->leftJoin('segImages', 'segImages.id', '=', 'segareas.source');

        return $query;
    }
    
    /**
     * Get the category associated with the area.
     */
    public function category()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Model\SegCategory','areaType');
    }
}
