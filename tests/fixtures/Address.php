<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: squigg
 * Date: 24/10/16
 * Time: 21:29
 */
class Address extends Model
{

    /* DO NOT USE THIS MODEL - FOR TESTING PURPOSES ONLY */

    public $filled;

    public function fill(array $attributes)
    {
        $this->attributes = $attributes;
        $this->filled = 'yes';
        return $this;
    }

    public function forceFill(array $attributes)
    {
        $this->attributes = $attributes;
        $this->filled = 'forced';
        return $this;
    }
}
