<?php
namespace CG\CourierAdapter\Provider\Implementation\Email;

use CG\CourierAdapter\EmailClientInterface;
use CG\Email\Mailer as Mailer;
use Zend\View\Model\ViewModel;

class Client implements EmailClientInterface
{
    /** @var Mailer */
    protected $mailer;
    /** @var ViewModel */
    protected $viewModel;

    public function __construct(Mailer $mailer, ViewModel $viewModel)
    {
        $this->setMailer($mailer)
            ->setViewModel($viewModel);
    }

    public function send($to, $subject, $message)
    {
        $viewModel = clone($this->viewModel);
        $viewModel->setTemplate('courier-adapter/email');
        $viewModel->setVariable('message', $message);

        $this->mailer->send($to, $subject, $viewModel);
    }

    protected function setMailer(Mailer $mailer)
    {
        $this->mailer = $mailer;
        return $this;
    }

    protected function setViewModel(ViewModel $viewModel)
    {
        $this->viewModel = $viewModel;
        return $this;
    }
}