<?php
namespace Application\Forms;

use ArrayAccess;
use Zend\Form\Factory as FormFactory;

class Factory
{
    protected $config = array();
    protected $formFactory;

    public function __construct($applicationConfig, FormFactory $formFactory)
    {
        $this->setConfigFromApplicationConfig($applicationConfig);
        $this->setFormFactory($formFactory);
    }

    protected function setConfigFromApplicationConfig($applicationConfig)
    {
        if (!is_array($applicationConfig) && !($applicationConfig instanceof ArrayAccess)) {
            return $this;
        }

        if (!isset($applicationConfig['forms'])) {
            return $this;
        }

        return $this->setConfig($applicationConfig['forms']);
    }

    public function setConfig($config)
    {
        if (!is_array($config) && !($config instanceof ArrayAccess)) {
            return $this;
        }

        $this->config = $config;
        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    public function has($form)
    {
        return isset($this->getConfig()[$form]);
    }

    public function get($form)
    {
        $formSpec = array();
        if ($this->has($form)) {
            $formSpec = $this->getConfig()[$form];
        }
        return $this->getFormFactory()->createForm($formSpec);
    }
} 