<?php
namespace SetupWizard\Controller;

use CG\Billing\Licence\Entity as Licence;
use CG\Billing\Package\Entity as Package;
use CG_Billing\Package\Exception as SetPackageException;
use CG_Billing\Package\ManagementService as PackageManagementService;
use CG_Billing\Payment\Service as PaymentService;
use CG_Billing\Payment\View\Service as PaymentViewService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Controller\Service as SetupService;
use SetupWizard\Payment\PackageService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;

class PaymentController extends AbstractActionController
{
    const ROUTE_PAYMENT = 'Payment';
    const ROUTE_PACKAGE_REMEMBER = 'PackageRemember';
    const ROUTE_PACKAGE_SET = 'PackageSet';

    /** @var SetupService */
    protected $setupService;
    /** @var PackageService */
    protected $packageService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var PaymentService */
    protected $paymentService;
    /** @var PaymentViewService */
    protected $paymentViewService;
    /** @var PackageManagementService */
    protected $packageManagementService;
    /** @var SessionManager */
    protected $session;

    public function __construct(
        Service $setupService,
        PackageService $packageService,
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        PaymentService $paymentService,
        PaymentViewService $paymentViewService,
        PackageManagementService $packageManagementService,
        SessionManager $session
    ) {
        $this->setupService = $setupService;
        $this->packageService = $packageService;
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->paymentService = $paymentService;
        $this->paymentViewService = $paymentViewService;
        $this->packageManagementService = $packageManagementService;
        $this->session = $session;
    }

    public function indexAction()
    {
        return $this->setupService->getSetupView('Add Payment Method', $this->getBody(), $this->getFooter());
    }

    protected function getBody(): ViewModel
    {
        return $this->viewModelFactory->newInstance()
            ->setTemplate('setup-wizard/payment/index')
            ->setVariable('locale', $this->packageService->getLocale())
            ->setVariable('selectedPackage', $this->getSelectedPackage())
            ->setVariable('packages', $this->getPackagesData())
            ->setVariable('activePaymentMethod', $this->paymentService->getPaymentMethod())
            ->addChild($this->paymentViewService->getPaymentMethodSelectView(), 'paymentMethod');
    }

    protected function getSelectedPackage(): ?int
    {
        return $this->session->getStorage()['setup-payment']['selected-package'] ?? false;
    }

    protected function getPackagesData(): array
    {
        $packages = [];
        foreach ($this->packageService->getSelectableOrderPackages() as $package) {
            $packages[] = [
                'id' => $package->getId(),
                'name' => $package->getName(),
                'band' => $package->getBand(),
                'price' => $this->packageService->getPackagePrice($package),
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
        ])->setTemplate('setup-wizard/payment/footer');
    }

    public function rememberPackageAction()
    {
        $storage = $this->session->getStorage();
        if (!isset($storage['setup-payment'])) {
            $storage['setup-payment'] = [];
        }
        $storage['setup-payment']['selected-package'] = $this->params()->fromRoute('id');
        return $this->jsonModelFactory->newInstance(['success' => true]);
    }

    public function setPackageAction()
    {
        $response = ['success' => false, 'error' => ''];
        try {
            $this->packageManagementService->setPackage($this->params()->fromRoute('id'));
            $response['success'] = true;
        } catch (SetPackageException\PricingSchemeMismatch $pricingSchemeMismatch) {
            $newPackage = $pricingSchemeMismatch->getPackage();
            throw new \RuntimeException(
                sprintf('Package \'%s\' (%s) is not available', $newPackage->getName(), $newPackage->getId()),
                0,
                $pricingSchemeMismatch
            );
        } catch (SetPackageException\MissingPaymentMethod $missingPaymentMethod) {
            $response['error'] = 'Please setup a payment method before selecting a package';
        } catch (SetPackageException\Failure $failure) {
            $response['error'] = sprintf(
                'Your %s could not be completed. There may be an issue with your payment details.'
                . '<br/>Please check them and try again.',
                $failure->getType()
            );
        } catch (\Throwable $throwable) {
            $response['error'] = $throwable->getMessage() ?? 'There was a problem with changing your package, please contact support.';
        }
        return $this->jsonModelFactory->newInstance($response);
    }
}