<?php
namespace Partner\Controller;

use Application\Controller\AbstractJsonController;
use CG\Account\Request\Entity as AccountRequest;
use CG\Partner\Entity as Partner;
use CG_Permission\Service as PermissionService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Partner\Account\AuthoriseService;
use Partner\Account\InvalidRequestException;
use Partner\Account\InvalidTokenException;
use Zend\Http\Header\SetCookie;
use Zend\View\Model\ViewModel;

class AccountController extends AbstractJsonController
{
    const ROUTE_AUTHORISE_ACCOUNT = 'authorise_account';

    /** @var AuthoriseService */
    protected $authoriseService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        AuthoriseService $authoriseService,
        ViewModelFactory $viewModelFactory
    ) {
        parent::__construct($jsonModelFactory);
        $this->authoriseService = $authoriseService;
        $this->viewModelFactory = $viewModelFactory;
    }

    public function indexAction()
    {
        try {
            $token = $this->params()->fromQuery('token', null);
            $signature = $this->params()->fromQuery('signature', null);
            $uri = $this->getRequest()->getUri();

            $accountRequest = $this->authoriseService->fetchAccountRequestForToken($token);
            $partner = $this->authoriseService->fetchPartner($accountRequest->getPartnerId(), $token);
            $redirectUrl = $this->authoriseService->connectAccount($accountRequest, $partner, $token, $signature, $uri);
            $this->setAccountRequestCookie($accountRequest);
            return $this->buildAccountConnectionViewModel($accountRequest, $partner, $redirectUrl);
        } catch (InvalidTokenException $e) {
            return $this->buildErrorResponse('Invalid request');
        } catch (InvalidRequestException $e) {
            $this->redirect()->toUrl($e->getRedirectUrl());
        } catch (\Throwable $e) {
            // This catch block prevents any undesired behaviour on this page if something goes wrong
            $this->logErrorException($e);
            return $this->buildErrorResponse('Invalid request');
        }
    }

    protected function setAccountRequestCookie(AccountRequest $accountRequest): void
    {
        $cookie = new SetCookie(PermissionService::PARTNER_MANAGED_LOGIN, $accountRequest->getId(), time() + 15 * 60);
        $this->getResponse()->getHeaders()->addHeader($cookie);
    }

    protected function buildAccountConnectionViewModel(
        AccountRequest $accountRequest,
        Partner $partner,
        string $redirectUrl
    ): ViewModel {
        $view = $this->viewModelFactory->newInstance([
            'partnerName' => $partner->getName(),
            'partnerLogoUrl' => $partner->getLogoUrl(),
            'channel' => $accountRequest->getChannel(),
            'region' => $accountRequest->getRegion(),
            'channelRedirectUrl' => $redirectUrl
        ]);

        return $view;
    }
}
