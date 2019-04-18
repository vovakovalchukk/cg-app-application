<?php
namespace Settings\Controller;

use Application\Controller\AbstractJsonController;
use CG_UI\View\Prototyper\JsonModelFactory;
use Settings\Account\AuthoriseService;
use Settings\Account\InvalidRequestException;
use Settings\Account\InvalidTokenException;

class AccountController extends AbstractJsonController
{
    const ROUTE_AUTHORISE_ACCOUNT = 'authorise_account';

    /** @var AuthoriseService */
    protected $authoriseService;

    public function __construct(JsonModelFactory $jsonModelFactory, AuthoriseService $authoriseService)
    {
        parent::__construct($jsonModelFactory);
        $this->authoriseService = $authoriseService;
    }

    public function indexAction()
    {
        try {
            $token = $this->params()->fromQuery('token', null);
            $signature = $this->params()->fromQuery('signature', null);
            $uri = $this->getRequest()->getUri();

            $this->authoriseService->connectAccount($token, $signature, $uri);

            // TODO: this will be removed by TAC-392 once the account creation process is kicked off at this point
            return $this->buildSuccessResponse();
        } catch (InvalidTokenException $e) {
            return $this->buildErrorResponse('Invalid request');
        } catch (InvalidRequestException $e) {
            $this->redirect()->toUrl($e->getRedirectUrl());
        } catch (\Throwable $e) {
            // This catch block prevents any undesired behaviour on this page if something goes wrong
            $this->logErrorException($e);
            return $this->buildErrorResponse(   'Invalid request');
        }
    }
}
