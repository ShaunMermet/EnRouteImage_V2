<?php
namespace UserFrosting\Sprinkle\Site\Sprunje;

use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Site\Model\ImgLinks;

class ImgLinksSprunje extends Sprunje
{
    protected $name = 'ImgLinks';

    protected $sortable = [
        'id',
        'validated_at'
    ];

    protected $filterable = [
        'group',
        'category',
        'state',
        'set_id'
    ];

    /**
     * Set the initial query used by your Sprunje.
     */
    protected function baseQuery()
    {
        $query = new ImgLinks();

        // Alternatively, if you have defined a class mapping, you can use the classMapper:
        // $query = $this->classMapper->createInstance('owl');

        return $query;
    }
    protected function filterGroup($query, $value)
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        return $query->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $query = $query->orLike('group', $value);
            }
            $query = $query->orWhereNull('group');
        });
    }
    protected function filterSetId($query, $value)
    {
        // Split value on separator for OR queries
        $values = explode($this->orSeparator, $value);
        return $query->where('set_id','=' , $value);
    }
}