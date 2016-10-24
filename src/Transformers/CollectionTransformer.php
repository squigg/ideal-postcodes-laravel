<?php namespace Squigg\IdealPostcodes\Transformers;

use Illuminate\Support\Collection;
use Squigg\IdealPostcodes\Transformers\Interfaces\AddressCollectionTransformer;

/**
 * Created by PhpStorm.
 * User: squigg
 * Date: 24/10/16
 * Time: 20:39
 */
class CollectionTransformer implements AddressCollectionTransformer
{

    /**
     * Transform an array of address
     *
     * @param array $addresses
     * @return mixed
     */
    public function transform(array $addresses)
    {
        $collection = new Collection($addresses);
        return $collection->keyBy('udprn');
    }
}
