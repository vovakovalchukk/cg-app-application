<?php
namespace Messages\Thread\Formatter;

use CG\Communication\Thread\Entity as Thread;
use CG\Communication\Thread\Collection as ThreadCollection;
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

    public function __invoke(ThreadCollection $threads, string $channel): ?FormatterInterface
    {
        if (!$this->validateCollection($threads, $channel)) {
            return null;
        }

        $formatterClass = __NAMESPACE__ . '\\' . ucfirst($channel);
        if (!class_exists($formatterClass)) {
            return null;
        }
        $formatter = $this->di->get($formatterClass);
        if (!$formatter instanceof FormatterInterface) {
            throw new \RuntimeException($formatterClass . ' must implement ' . FormatterInterface::class);
        }

        return $formatter;
    }

    protected function validateCollection(ThreadCollection $threads, string $channel): bool
    {
        /** @var Thread $thread */
        foreach ($threads as $thread) {
            if ($thread->getChannel() !== $channel) {
                return false;
            }
        }

        return true;
    }
}