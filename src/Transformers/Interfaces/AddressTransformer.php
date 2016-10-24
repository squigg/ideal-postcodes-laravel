<?php
/**
 * Created by PhpStorm.
 * User: squigg
 * Date: 24/10/16
 * Time: 20:40
 */

namespace Squigg\IdealPostcodes\Transformers\Interfaces;


interface AddressTransformer
{

    /**
     * Transform an address
     *
     * @param array $address
     * @return mixed
     */
    public function transform(array $address);

}
