<?php

namespace Picqer\BolRetailerV4\Model;

// This class is auto generated by OpenApi\ModelGenerator
class ReducedOrderItem extends AbstractModel
{
    protected static $modelDefinition = [
        'orderItemId' => ['model' => null, 'array' => false ],
        'ean' => ['model' => null, 'array' => false ],
        'quantity' => ['model' => null, 'array' => false ],
    ];

    /**
     * @var string The id for the order item (1 order can have multiple order items).
     */
    public $orderItemId;

    /**
     * @var string The EAN number associated with this product.
     */
    public $ean;

    /**
     * @var int Amount of ordered products for this order item id.
     */
    public $quantity;
}
