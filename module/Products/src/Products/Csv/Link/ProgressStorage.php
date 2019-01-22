<?php
namespace Products\Csv\Link;

use Predis\Client as Predis;
use Products\Csv\ProgressStorageAbstract;

class ProgressStorage extends ProgressStorageAbstract
{
    const KEY_PREFIX = 'ProductLinkExportProgress:';
    const KEY_PREFIX_TOTAL = 'Total:';
    const KEY_EXPIRY_SEC = 30;

    public function __construct(Predis $predis)
    {
        parent::__construct($predis);
    }
}