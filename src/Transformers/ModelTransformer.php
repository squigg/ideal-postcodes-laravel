<?php namespace Squigg\IdealPostcodes\Transformers;

use Illuminate\Database\Eloquent\Model;
use Squigg\IdealPostcodes\Transformers\Interfaces\AddressTransformer;

/**
 * Created by PhpStorm.
 * User: squigg
 * Date: 24/10/16
 * Time: 20:39
 */
class ModelTransformer implements AddressTransformer
{

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var bool
     */
    protected $forceFill;

    /**
     * ModelTransformer constructor.
     * @param Model $modelClass
     * @param bool $forceFill
     */
    public function __construct($modelClass, $forceFill = false)
    {
        if (!is_a($modelClass, Model::class, true)) {
            throw new \InvalidArgumentException("IdealPostcodes: $modelClass given as modelTransformerOption must be a Laravel Model");
        }

        $this->modelClass = $modelClass;
        $this->forceFill = $forceFill;
    }

    /**
     * Transform an address
     *
     * @param array $address
     * @return mixed
     */
    public function transform(array $address)
    {
        /** @var Model $model */
        $model = new $this->modelClass;
        if ($this->forceFill) {
            $model->forceFill($address);
        }
        else {
            $model->fill($address);
        }
        return $model;
    }
}
