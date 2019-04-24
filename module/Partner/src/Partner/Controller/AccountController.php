<?php
namespace Partner\Controller;

use Application\Controller\AbstractJsonController;
use CG\Account\Request\Entity as AccountRequest;
use CG\Channel\Type as ChannelType;
use CG\Partner\Entity as Partner;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Partner\Account\AuthoriseService;
use Partner\Account\InvalidRequestException;
use Partner\Account\InvalidTokenException;
use Settings\Channel\Service as ChannelService;
use Zend\View\Model\ViewModel;

class AccountController extends AbstractJsonController
{
    const ROUTE_AUTHORISE_ACCOUNT = 'authorise_account';

    /** @var AuthoriseService */
    protected $authoriseService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var ChannelService */
    protected $channelService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        AuthoriseService $authoriseService,
        ViewModelFactory $viewModelFactory,
        ChannelService $channelService
    ) {
        parent::__construct($jsonModelFactory);
        $this->authoriseService = $authoriseService;
        $this->viewModelFactory = $viewModelFactory;
        $this->channelService = $channelService;
    }

    public function indexAction()
    {
        try {
            $token = $this->params()->fromQuery('token', null);
            $signature = $this->params()->fromQuery('signature', null);
            $uri = $this->getRequest()->getUri();

            $accountRequest = $this->authoriseService->fetchAccountRequestForToken($token);
            $partner = $this->authoriseService->fetchPartner($accountRequest->getPartnerId(), $token);
            $this->authoriseService->connectAccount($accountRequest, $partner, $token, $signature, $uri);

            return $this->buildAccountConnectionViewModel($accountRequest, $partner);
        } catch (InvalidTokenException $e) {
            return $this->buildErrorResponse('Invalid request');
        } catch (InvalidRequestException $e) {
            $this->redirect()->toUrl($e->getRedirectUrl());
        } catch (\Throwable $e) {
            // This catch block prevents any undesired behaviour on this page if something goes wrong
            $this->logErrorException($e);
        }

        return $this->buildErrorResponse('Invalid request');
    }

    protected function buildAccountConnectionViewModel(AccountRequest $accountRequest, Partner $partner): ViewModel
    {
        $redirectUrl = $this->channelService->createAccount(
            ChannelType::SALES,
            $accountRequest->getChannel(),
            $accountRequest->getRegion()
        );

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
