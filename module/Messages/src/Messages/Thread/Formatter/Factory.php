<?php
namespace Messages\Thread\Formatter;

use CG\Communication\Thread\Entity as Thread;
use Messages\Thread\FormatterInterface;
use Zend\Di\Di;
use function CG\Stdlib\hyphenToClassname;

class Factory
{
    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(Thread $thread): ?FormatterInterface
    {
        $formatterClass = __NAMESPACE__ . '\\' . hyphenToClassname($thread->getChannel());
        if (!class_exists($formatterClass)) {
            return null;
        }
        $formatter = $this->di->get($formatterClass);
        if (!$formatter instanceof FormatterInterface) {
            throw new \RuntimeException($formatterClass . ' must implement ' . FormatterInterface::class);
        }
        return $formatter;
    }
}