<?php
namespace CG\CourierAdapter\Provider\Account;

use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as OHAccount;
use CG\CourierAdapter\Account as CAAccount;

class Mapper
{
    /** @var Cryptor */
    protected $cryptor;

    public function __construct(Cryptor $cryptor)
    {
        $this->setCryptor($cryptor);
    }

    public function fromOHAccount(OHAccount $ohAccount)
    {
        return $this->fromArray([
            $this->cryptor->decrypt($ohAccount->getCredentials())->toArray(),
            (isset($ohAccount->getExternalData()['config']) ? json_decode($ohAccount->getExternalData()['config']) : []),
        ]);
    }

    public function fromArray(array $data)
    {
        return new CAAccount(
            $data['credentials'],
            $data['config']
        );
    }

    protected function setCryptor(Cryptor $cryptor)
    {
        $this->cryptor = $cryptor;
        return $this;
    }
}
