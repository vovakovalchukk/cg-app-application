<?php
namespace Settings\Controller;

use Settings\Account\AuthoriseService;
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
            $this->authoriseService->validateToken($token, $signature, $uri);
        } catch (InvalidTokenException $e) {
            // TODO: redirect to partner's failure URL with status code
        }
    }
}
