<?php
return array(
    'mustache' => array(
        'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/../../public'  . CG_UI\Module::PUBLIC_FOLDER . 'templates')
    ),
);