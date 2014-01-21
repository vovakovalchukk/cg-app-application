<?php
namespace CG\Skeleton\Composer;

use CG\Skeleton\Console;
use CG\Skeleton\Module\BaseConfig;

class Composer
{
    protected $path;
    protected $data;
    protected $requireData;
    protected $console;

    public function __construct(Console $console, $path)
    {
        $this->setPath($path)
             ->setConsole($console)
             ->load()
             ->setRequireData($this->getData()['require']);
    }

    public function setConsole($console)
    {
        $this->console = $console;
        return $this;
    }

    public function getConsole()
    {
        return $this->console;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setRequireData(&$requireData)
    {
        $this->requireData =& $requireData;
        return $this;
    }

    public function &getRequireData()
    {
        return $this->requireData;
    }

    public function save()
    {
        file_put_contents($this->getPath(), json_encode($this->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function updateComposer($requires = null)
    {
        $this->getConsole()->writeln(Console::COLOR_GREEN . ' + ' . "Updating composer..." . Console::COLOR_GREEN);

        $output = array();
        $return = 0;
        if (!is_null($requires)) {
            if (empty($requires)) {
                return;
            }
            $this->getConsole()->writeln("\t* " . implode("\n\t* ",$requires));
            $packageNames = array();
            foreach ($requires as $require) {
                $requireExplode = $this->explodeRequireString($require);
                $packageNames[] = $requireExplode[0];
            }
            exec('php composer.phar update ' . implode(' ', $packageNames), $output, $return);
        } else {
            exec('php composer.phar update', $output, $return);
        }

        if ($return != 0) {
            foreach($output as $line) {
                echo $line . "\n";
            }
        }
    }

    public function addRequires(BaseConfig $moduleConfig, $requires = array(), $update = true)
    {
        $requiresToUpdate = array();
        foreach ($requires as $require) {
            if ($this->addRequire($moduleConfig, $require, false)) {
                $requiresToUpdate[] = $require;
            }
        }

        if ($update) {
            $this->updateComposer($requiresToUpdate);
        }
    }

    public function addRequire(BaseConfig $moduleConfig, $require, $update = true)
    {
        $updateRequired = false;
        $requireExplode = $this->explodeRequireString($require);

        $newVersion = $this->requireExists($require) ?
            $this->getRequireVersion($require) != $requireExplode[1] : false;

        $skeletonCommittedLastRequire = $this->requireExists($require) ?
            $this->getRequireVersion($require) == $moduleConfig->getComposerRequireVersion($requireExplode[0]) : false;

        if (!$this->requireExists($require) || ($newVersion && $skeletonCommittedLastRequire)) {
            $beforeHash = hash_file('md5', 'composer.json');
            exec('php composer.phar require --no-update ' . $require);
            $afterHash = hash_file('md5', 'composer.json');
            $hasComposerJsonChanged = ($beforeHash != $afterHash);

            if ($update && $hasComposerJsonChanged) {
                $this->updateComposer(array($require));
                $updateRequired = false;
            } else if ($hasComposerJsonChanged) {
                $updateRequired = true;
            }

            $moduleConfig->setComposerRequire($requireExplode[0], $requireExplode[1]);
            $this->load();
            return $updateRequired;
        }

        $moduleConfig->setComposerRequire($requireExplode[0], $requireExplode[1]);
        return false;
    }

    public function requireExists($require)
    {
        $requireExplode = $this->explodeRequireString($require);
        $requireData = $this->getRequireData();

        $packageName = $requireExplode[0];
        foreach ($requireData as $name => $version) {
            if ($name == $packageName) {
                return true;
            }
        }
        return false;
    }

    public function removeRequires(BaseConfig $moduleConfig, array $requires, $update = true)
    {
        foreach ($requires as $require) {
            $this->removeRequire($moduleConfig, $require, false);
        }

        if ($update) {
            $this->updateComposer($requires);
        }
    }

    public function removeRequire(BaseConfig $moduleConfig, $require, $update = true)
    {
        $requireExplode = $this->explodeRequireString($require);
        $requireData =& $this->getRequireData();

        if($this->requireExists($require)) {
            unset($requireData[$requireExplode[0]]);
        }

        if ($update) {
            $this->updateComposer(array($require));
        }

        $this->save();
        $moduleConfig->removeComposerRequire($requireExplode[0]);
        return $this;
    }

    protected function explodeRequireString($require)
    {
        return explode(':', $require);
    }

    protected function getRequireVersion($require)
    {
        $requireExplode = $this->explodeRequireString($require);
        return $this->getRequireData()[$requireExplode[0]];
    }

    protected function load()
    {
        if (!is_file($this->getPath())) {
            return $this;
        }

        $jsonData = json_decode(file_get_contents($this->getPath()), true);
        if (!is_array($jsonData)) {
            return $this;
        }

        $this->setData($jsonData);
        return $this;
    }
}