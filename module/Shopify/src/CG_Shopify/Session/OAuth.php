<?php
namespace CG_Shopify\Session;

use Zend\Stdlib\ArrayObject;

class OAuth extends ArrayObject
{
    const KEY_NONCE = 'nonce';
    const KEY_ACCOUNT_ID = 'accountId';

    public function __construct($accountId = null, $nonce = null)
    {
        parent::__construct(
            [
                static::KEY_ACCOUNT_ID => $accountId,
                static::KEY_NONCE => $nonce,
            ]
        );
    }

    public function getAccountId()
    {
        return $this[static::KEY_ACCOUNT_ID];
    }

    public function getNonce()
    {
        return $this[static::KEY_NONCE];
    }
} 
