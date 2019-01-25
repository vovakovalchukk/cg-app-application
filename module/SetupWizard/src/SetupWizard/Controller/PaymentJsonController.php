<?php
namespace SetupWizard\Controller;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\I18n\Translator\Translator;
use Zend\Mvc\Controller\AbstractActionController;
use CG_Billing\Package\Service as PackageService;
use SetupWizard\Module;

class PaymentJsonController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    public const ROUTE_APPLY_DISCOUNT_CODE = 'ApplyCode';

    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var Translator  */
    protected $translator;
    /** @var PackageService */
    protected $packageService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        Translator $translator,
        PackageService $packageService
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->translator = $translator;
        $this->packageService = $packageService;
    }

    public function applyDiscountCodeAction()
    {
        $discountCode = $this->params()->fromPost('discountCode', null);
        if (!$discountCode) {
            $message = $this->translator->translate('No discount code was supplied');
            return $this->jsonModelFactory->newInstance(['message' => $message]);
        }

        if ($this->packageService->isValidDiscountCode($discountCode)) {
            $redirect = $this->url()->fromRoute(
                Module::ROUTE . '/' . PaymentController::ROUTE_PAYMENT
            );
            $redirect .= '?' . http_build_query(['discountCode' => $discountCode]);
            return $this->jsonModelFactory->newInstance(['redirect' => $redirect]);
        }

        $message = $this->translator->translate($discountCode . ' is not a valid discount code');
        return $this->jsonModelFactory->newInstance(['message' => $message]);
    }
}