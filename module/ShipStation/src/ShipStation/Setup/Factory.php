<?php
namespace ShipStation\Setup;

use ShipStation\SetupInterface;
use ShipStation\Setup\Other;
use function CG\Stdlib\hyphenToClassname;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Di\Di;
use Zend\Mvc\Controller\Plugin\Redirect as RedirectHelper;
use Zend\Mvc\Controller\Plugin\Url as UrlHelper;

class Factory
{
    /** @var DI */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(
        string $channel,
        ViewModelFactory $viewModelFactory,
        UrlHelper $urlHelper,
        RedirectHelper $redirectHelper
    ): ?SetupInterface {
        $className = __NAMESPACE__ . '\\' . $this->getClassNameForChannel($channel);
        if (!class_exists($className)) {
            $className = Other::class;
        }
        $class = $this->di->get($className, [
            'viewModelFactory' => $viewModelFactory,
            'urlHelper' => $urlHelper,
            'redirectHelper' => $redirectHelper
        ]);
        if (!$class instanceof SetupInterface) {
            throw new \RuntimeException($className . ' does not implement ' . SetupInterface::class);
        }
        return $class;
    }

    protected function getClassNameForChannel(string $channel)
    {
        return hyphenToClassname(preg_replace('/-ss$/', '', $channel));
    }
}