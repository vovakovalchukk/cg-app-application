<?php
namespace SetupWizard\Controller;

use CG\Billing\Licence\Entity as Licence;
use CG\Billing\Package\Entity as Package;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Controller\Service as SetupService;
use SetupWizard\Payment\PackageService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PaymentController extends AbstractActionController
{
    const ROUTE_PAYMENT = 'Payment';
    const ROUTE_PACKAGE = 'Package';

    /** @var SetupService */
    protected $setupService;
    /** @var PackageService */
    protected $packageService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;

    public function __construct(
        Service $setupService,
        PackageService $packageService,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory
    ) {
        $this->setupService = $setupService;
        $this->packageService = $packageService;
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
    }

    public function indexAction()
    {
        return $this->setupService->getSetupView('Add Payment Method', $this->getBody(), $this->getFooter());
    }

    protected function getBody(): ViewModel
    {
        return $this->viewModelFactory->newInstance([
            'packages' => $this->getPackagesData(),
        ])->setTemplate('setup-wizard/payment/index');
    }

    protected function getPackagesData(): array
    {
        $packages = [];
        foreach ($this->packageService->getSelectableOrderPackages() as $package) {
            $packages[] = [
                'id' => $package->getId(),
                'name' => $package->getName(),
                'price' => $package->getPrice(),
                'orderVolume' => $this->getOrderVolumeForPackage($package),
            ];
        }
        usort(
            $packages,
            function(array $a, array $b) {
                if ($a['orderVolume'] > $b['orderVolume']) {
                    return 1;
                }
                if ($a['orderVolume'] < $b['orderVolume']) {
                    return -1;
                }
                return 0;
            }
        );
        return $packages;
    }

    protected function getOrderVolumeForPackage(Package $package): int
    {
        $orderVolume = 0;
        /** @var Licence $licence */
        foreach ($package->getLicences() as $licence) {
            if ($licence->getType() !== Licence::TYPE_ORDER) {
                continue;
            }
            $orderVolume += $licence->getAmount();
        }
        return $orderVolume;
    }

    protected function getFooter(): ViewModel
    {
        return $this->viewModelFactory->newInstance([
            'buttons' => $this->setupService->getNextButtonViewConfig(),
        ])->setTemplate('elements/buttons.mustache');
    }

    public function setPackageAction()
    {
        return $this->jsonModelFactory->newInstance(['success' => true, 'error' => '']);
    }
}