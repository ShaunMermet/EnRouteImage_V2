<?php
namespace UserFrosting\Sprinkle\Site\Sprunje;

use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Site\Database\Models\SegArea;

class SegAreaSprunje extends Sprunje
{
    protected $name = 'SegArea';

    /**
     * Set the initial query used by your Sprunje.
     */
    protected function baseQuery()
    {
        $query = new SegArea();

        // Alternatively, if you have defined a class mapping, you can use the classMapper:
        // $query = $this->classMapper->createInstance('owl');

        return $query;
    }
    protected function applyTransformations($collection)
    {
        // Exclude password field from results
        $collection->transform(function ($item, $key) {
            //unset($item['data']);
            $item['data'] = unserialize($item['data']);
            return $item;
        });

        return $collection;
    }
}