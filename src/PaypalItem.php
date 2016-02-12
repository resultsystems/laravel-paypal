<?php

namespace ResultSystems\Paypal;

class PaypalItem
{
    public $name;
    public $description;
    public $amount;
    public $quantity;
    public $item_amount;

    /**
     * Objeto pedido.
     *
     * @param float     $quantity       Quantidade de produtos
     * @param string    $name           Nome do produto
     * @param string    $description    Descrição do produto
     * @param float     $amount         Valor do produto
     */
    public function __construct($quantity, $name, $description, $amount)
    {
        if (!is_numeric($quantity) || $quantity <= 0) {
            throw new PaypalRequestException("Quantity '${quantity}' is not valid");
        }

        if (!is_numeric($quantity) || $quantity <= 0) {
            throw new PaypalRequestException("Quantity '${quantity}' is not valid");
        }

        $this->name = $name;
        $this->description = $description;
        $this->amount = (double) $amount;
        $this->quantity = (double) $quantity;
        $this->item_amount = $amount;
    }
}
