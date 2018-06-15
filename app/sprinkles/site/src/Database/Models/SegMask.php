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
 * Represents an mask drawn on an image imgLink.
 * @author Shaun Mermet ()
 * @property string source
 * @property string data
 * @property string user
 */
class SegMask extends Model
{

    use SoftDeletes;


    protected $dates = ['deleted_at'];
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "segmasks";

    protected $fillable = [
        "source",
        "user",
        "NbrSeg",
        "compactness",
        "segInfo",
        "SlicStr"
        
    ];


    /**
     * Get the image that owns the area.
     */
    public function segImage()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Database\Models\SegImage', 'source');
    }

    /**
     * Joins the img related to directly, so we can do things like sort, search, paginate, etc.
     */
    /*public function scopeJoinSegImage($query)
    {
        //$query = $query->select('segareas.*');

        //$query = $query->leftJoin('segImages', 'segImages.id', '=', 'segareas.source');

        return $query;
    }*/
    
    /**
     * Get the category associated with the area.
     */
    /*public function category()
    {
        return $this->belongsTo('UserFrosting\Sprinkle\Site\Database\Models\SegCategory','areaType');
    }*/
}
