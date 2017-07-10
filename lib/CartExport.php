<?php

class CartExport
{
    /**
     * @param CartCore $cartCore
     *
     * @return array
     */
    public static function export(CartCore $cartCore)
    {
        if (_PS_VERSION_ >= 1.7) {
            return [
                'orderId' => $cartCore->id,
                'items' => $cartCore->getProducts(true),
                'amount' => $cartCore->getOrderTotal(),
                'shipping' => $cartCore->getTotalShippingCost(),
                'summary' => json_decode(json_encode($cartCore->getSummaryDetails()))
            ];
        }

        if (_PS_VERSION_ < 1.7) {
            return [
                'orderId' => $cartCore->id,
                'items' => $cartCore->getProducts(true),
                'amount' => $cartCore->getOrderTotal(),
                'shipping' => $cartCore->getTotalShippingCost(),
                'summary' => json_decode(json_encode($cartCore->getSummaryDetails()))
            ];
        }
        return [];
    }
}
