<?php
namespace Settings\Controller;

use Settings\Account\AuthoriseService;
use Settings\Account\InvalidRequestException;
use Settings\Account\InvalidTokenException;
use Zend\Mvc\Controller\AbstractActionController;

class AccountController extends AbstractActionController
{
    const ROUTE_AUTHORISE_ACCOUNT = 'authorise_account';

    /** @var AuthoriseService */
    protected $authoriseService;

    public function __construct(AuthoriseService $authoriseService)
    {
        $this->authoriseService = $authoriseService;
    }

    public function indexAction()
    {
        try {
            $token = $this->params()->fromQuery('token', null);
            $signature = $this->params()->fromQuery('signature', null);
            $uri = $this->getRequest()->getUri();
            $this->authoriseService->validateRequest($token, $signature, $uri);
        } catch (InvalidTokenException $e) {
            // TODO: do something about the missing token
        } catch (InvalidRequestException $e) {
            $this->redirect()->toUrl($e->getRedirectUrl());
        }
    }
}
