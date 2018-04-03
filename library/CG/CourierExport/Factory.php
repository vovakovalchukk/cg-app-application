<?php
namespace CG\CourierExport;

use CG\Account\Shared\Entity as Account;
use CG\Stdlib\Exception\Runtime\NotFound;
use function CG\Stdlib\hyphenToClassname;
use Zend\Di\Di;

class Factory
{
    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function getCreationService(string $channel, string $channelName): CreationService
    {
        $class = $this->getClassName($channel, 'CreationService');
        if (!class_exists($class) || !is_a($class, CreationService::class, true)) {
            $class = CreationService::class;
        }
        return $this->di->newInstance($class, [
            'channel' => $channel,
            'channelName' => $channelName,
            'cryptor' => 'courierexport_cryptor',
            'channelAccount' => $this->getClassName($channel, 'Account')
        ]);
    }

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
        $class = $this->getClassName($account->getChannel(), $className);
        if (!class_exists($class) || !is_a($class, $interface, true)) {
            throw new NotFound(sprintf('Unsupported account "%s"', $account->getChannel()));
        }
        return new $class();
    }

    protected function getClassName(string $channel, string $className): string
    {
        return __NAMESPACE__ . '\\' . hyphenToClassname($channel) . '\\' . $className;
    }
}