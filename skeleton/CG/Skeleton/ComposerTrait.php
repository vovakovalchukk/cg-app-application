<?php
namespace CG\Skeleton;

use CG\Skeleton\Module\BaseConfig;
use CG\Skeleton\Console;

trait ComposerTrait
{
    public function updateComposer(BaseConfig $moduleConfig,  $requires)
    {
        if ($moduleConfig->isEnabled()) {
            $beforeHash = hash_file('md5', 'composer.json');
            foreach($requires as $require) {
                exec('php composer.phar require --no-update ' . $require);
            }
            $afterHash = hash_file('md5', 'composer.json');

            if ($beforeHash != $afterHash) {
                $this->getConsole()->writeln(Console::COLOR_GREEN . ' + ' . "Updating composer...\n\t* "
                                             . implode("\n\t* ",$requires) . Console::COLOR_GREEN);
                exec('php composer.phar update predis/predis channelgrabber/predis');
            }
        }
    }

    abstract public function getConsole();
}