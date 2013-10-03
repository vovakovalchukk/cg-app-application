<?php
$modules = array(
    'CG\Skeleton\Module\Db\Module'
);

return array(
    'definition' => array(
        'class' => array(
            'CG\Skeleton\Application' => array(
                'addStartupCommand' => array(
                    'command' => array('type' => 'CG\Skeleton\StartupCommandInterface', 'required' => true)
                ),
                'addCommand' => array(
                    'command' => array('type' => 'CG\Skeleton\CommandInterface', 'required' => true)
                ),
                'addShutdownCommand' => array(
                    'command' => array('type' => 'CG\Skeleton\ShutdownCommandInterface', 'required' => true)
                )
            ),
            'CG\Skeleton\Module\StartupCommand' => array(
                'addModule' => array(
                    'module' => array('type' => 'CG\Skeleton\Module\ConfigureInterface', 'required' => true)
                )
            ),
            'CG\Skeleton\Module\Command' => array(
                'addModule' => array(
                    'module' => array('type' => 'CG\Skeleton\Module\ModuleInterface', 'required' => true)
                )
            )
        )
    ),
    'instance' => array(
        'CG\Skeleton\Application' => array(
            'injections' => array(
                'CG\Skeleton\Environment\StartupCommand',
                'CG\Skeleton\Vagrant\StartupCommand',
                'CG\Skeleton\Chef\StartupCommand',
                'CG\Skeleton\Module\StartupCommand',
                'CG\Skeleton\Vagrant\Command\Up',
                'CG\Skeleton\Vagrant\Command\Provision',
                'CG\Skeleton\Vagrant\Command\Reload',
                'CG\Skeleton\Vagrant\Command\Halt',
                'CG\Skeleton\Module\Command',
                'CG\Skeleton\Environment\ShutdownCommand'
            ),
            'shared' => true
        ),
        'CG\Skeleton\Vagrant\NodeData' => array(
            'parameters' => array(
                'path' => 'data/nodeData.json'
            ),
            'shared' => true
        ),
        'CG\Skeleton\Module\StartupCommand' => array(
            'injections' => $modules
        ),
        'CG\Skeleton\Module\Command' => array(
            'injections' => $modules
        )
    )
);