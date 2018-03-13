<?php
namespace CG\CourierExport;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\ServicesInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use function CG\Stdlib\hyphenToClassname;

class Factory
{
    public function getExportOptionsForAccount(Account $account): ExportOptionsInterface
    {
        return $this->getClassForAccount($account, ExportOptionsInterface::class, 'ExportOptions');
    }

    public function getExporterForAccount(Account $account): ExporterInterface
    {
        return $this->getClassForAccount($account, ExporterInterface::class, 'Exporter');
    }

    protected function getClassForAccount(Account $account, string $interface, string $className)
    {
        $class = __NAMESPACE__ . '\\' . hyphenToClassname($account->getChannel()) . '\\' . $className;
        if (!class_exists($class) || !is_a($class, $interface, true)) {
            throw new NotFound(sprintf('Unsupported account "%s"', $account->getChannel()));
        }
        return new $class();
    }
}