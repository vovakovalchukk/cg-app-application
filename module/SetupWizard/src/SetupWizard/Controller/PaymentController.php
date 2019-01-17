<?php
namespace SetupWizard\Controller;

use CG\Billing\Licence\Entity as Licence;
use CG\Billing\Package\Entity as Package;
use CG\Billing\Price\Service as PriceService;
use CG\Billing\Subscription\Entity as Subscription;
use CG\Locale\DemoLink;
use CG\Locale\PhoneNumber;
use CG\Payment\Exception\MultipleSubscriptionsException;
use CG_Billing\Package\Exception as SetPackageException;
use CG_Billing\Package\ManagementService as PackageManagementService;
use CG_Billing\Payment\Service as PaymentService;
use CG_Billing\Payment\View\Service as PaymentViewService;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use SetupWizard\Controller\Service as SetupService;
use SetupWizard\Payment\PackageService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;

class PaymentController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_PAYMENT = 'Payment';
    const ROUTE_PACKAGE_REMEMBER = 'PackageRemember';
    const ROUTE_BILLING_DURATION_REMEMBER = 'BillingDurationRemember';
    const ROUTE_PACKAGE_SET = 'PackageSet';

    const LOG_CODE = 'SetupWizardPaymentController';

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
        $locale = $this->packageService->getLocale();
        $body = $this->viewModelFactory->newInstance()
            ->setTemplate('setup-wizard/payment/index')
            ->setVariable('locale', $locale)
            ->setVariable('phoneNumber', PhoneNumber::getForLocale($locale))
            ->setVariable('selectedPackage', $this->getSelectedPackage())
            ->setVariable('selectedBillingDuration', $this->getSelectedBillingDuration())
            ->setVariable('packages', $this->getPackagesData())
            ->setVariable('activePaymentMethod', $this->paymentService->getPaymentMethod())
            ->setVariable('demoLink', DemoLink::getForLocale($locale))
            ->setVariable('takePayment', (bool) $this->params()->fromQuery('cardAuth'));

        if (!$this->paymentViewService->isSinglePaymentMethod()) {
            return $body->addChild($this->paymentViewService->getPaymentMethodSelectView(), 'paymentMethodSelect');
        }

        return $body->addChild(
            $this->viewModelFactory->newInstance()
                ->setTemplate('setup-wizard/payment/method')
                ->setVariable('method', $this->paymentViewService->getDefaultPaymentProvider()),
            'paymentMethod'
        );
    }

    protected function getSelectedPackage(): ?int
    {
        return $this->session->getStorage()['setup-payment']['selected-package'] ?? false;
    }

    protected function getSelectedBillingDuration(): int
    {
        return $this->session->getStorage()['setup-payment']['selected-duration'] ?? Subscription::DEFAULT_BILLING_DURATION;
    }

    protected function getPackagesData(): array
    {
        $packages = [];
        foreach ($this->packageService->getSelectableOrderPackages() as $package) {
            $packages[] = [
                'id' => $package->getId(),
                'name' => $package->getName(),
                'band' => $package->getBand(),
                'monthlyPrice' => [
                    PriceService::BILLING_DURATION_MONTHLY => $this->packageService->getPackageMonthlyPrice(
                        $package,
                        PriceService::BILLING_DURATION_MONTHLY
                    ),
                    PriceService::BILLING_DURATION_ANNUAL => $this->packageService->getPackageMonthlyPrice(
                        $package,
                        PriceService::BILLING_DURATION_ANNUAL
                    ),
                ],
                'price' => [
                    PriceService::BILLING_DURATION_MONTHLY => $this->packageService->getPackagePrice(
                        $package,
                        PriceService::BILLING_DURATION_MONTHLY
                    ),
                    PriceService::BILLING_DURATION_ANNUAL => $this->packageService->getPackagePrice(
                        $package,
                        PriceService::BILLING_DURATION_ANNUAL
                    ),
                ],
                'orderVolume' => $package->getLicences()->getTotalLicenceAmount(Licence::TYPE_ORDER),
                'forceBillingDuration' => $this->getForcedBillingDurationForPackage($package),
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

    protected function getForcedBillingDurationForPackage(Package $package): ?int
    {
        // Don't allow annual billing for free packages
        if ($package->getPrice() <= 0) {
            return Subscription::MIN_BILLING_DURATION;
        }
        return null;
    }

    protected function getFooter(): ViewModel
    {
        return $this->viewModelFactory->newInstance([
            'buttons' => array_merge(
                $this->setupService->getNextButtonViewConfig(),
                ['value' => 'Pay now']
            ),
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

    public function rememberBillingDurationAction()
    {
        $storage = $this->session->getStorage();
        if (!isset($storage['setup-payment'])) {
            $storage['setup-payment'] = [];
        }
        $storage['setup-payment']['selected-duration'] = $this->params()->fromRoute('duration');
        return $this->jsonModelFactory->newInstance(['success' => true]);
    }

    public function setPackageAction()
    {
        $response = ['success' => false, 'error' => ''];
        try {
            $packageId = $this->params()->fromRoute('id');
            $billingDuration = $this->params()->fromPost('billingDuration') ?? null;
            $billingDuration = $this->determineBillingDurationToSetForPackage($packageId, $billingDuration);

            $this->packageManagementService->setPackage(
                $this->packageManagementService->createPackageUpgradeRequest(
                    $packageId,
                    null,
                    $billingDuration
                )
            );
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
        } catch (MultipleSubscriptionsException $multipleSubscriptions) {
            $this->logDebugException($multipleSubscriptions, '', [], static::LOG_CODE);
            $response['success'] = true;
        } catch (\Throwable $throwable) {
            $response['error'] = $throwable->getMessage() ?? 'There was a problem with changing your package, please contact support.';
        }
        return $this->jsonModelFactory->newInstance($response);
    }

    protected function determineBillingDurationToSetForPackage(int $packageId, ?int $billingDuration): ?int
    {
        $package = $this->packageService->fetch($packageId);
        $forcedBillingDuration = $this->getForcedBillingDurationForPackage($package);
        if ($forcedBillingDuration) {
            return $forcedBillingDuration;
        }
        return $billingDuration;
    }
}