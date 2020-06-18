<?php

/**
 * Class WalletMercadopagePS_Model_PaymentMethodsMercadopage
 *
 */
class WalletMercadopagePS_Model_PaymentMethodsMercadopage extends Core_Model_Default
{
    /**
     * WalletMercadopagePS_Model_PaymentMethodsMercadopage constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'WalletMercadopagePS_Model_Db_Table_PaymentMethodsMercadopage';
        return $this;
    }
}