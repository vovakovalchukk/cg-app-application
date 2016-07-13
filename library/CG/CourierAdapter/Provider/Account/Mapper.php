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
            'credentials' => $this->cryptor->decrypt($ohAccount->getCredentials())->toArray(),
            'config' => (isset($ohAccount->getExternalData()['config']) ? json_decode($ohAccount->getExternalData()['config']) : []),
            'id' => $ohAccount->getExternalId(),
        ]);
    }

    public function fromArray(array $data)
    {
        return new CAAccount(
            $data['credentials'],
            (isset($data['config']) ? $data['config'] : []),
            (isset($data['id']) ? $data['id'] : null)
        );
    }

    protected function setCryptor(Cryptor $cryptor)
    {
        $this->cryptor = $cryptor;
        return $this;
    }
}
