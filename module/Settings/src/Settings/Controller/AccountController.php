<?php
namespace Settings\Controller;

use Settings\Account\AuthoriseService;
use Zend\Mvc\Controller\AbstractActionController;
use Settings\Account\InvalidTokenException;

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
            $this->authoriseService->validateToken($token);
        } catch (InvalidTokenException $e) {
            // TODO: redirect to partner's failure URL with status code
        }
    }
}
