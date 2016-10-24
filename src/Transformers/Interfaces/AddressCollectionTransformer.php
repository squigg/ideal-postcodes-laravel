<?php
/**
 * Created by PhpStorm.
 * User: squigg
 * Date: 24/10/16
 * Time: 20:40
 */

namespace Squigg\IdealPostcodes\Transformers\Interfaces;


interface AddressCollectionTransformer
{

    /**
     * Transform an array of address
     *
     * @param array $addresses
     * @return mixed
     */
    public function transform(array $addresses);

}
