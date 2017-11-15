<?php
return array(
  'controllers' => array(
      'invokables' => array(
          'ConfigLoader\Controller\ConfigLoader' => 'ConfigLoader\Controller\ConfigLoaderController',
        

      ),
  ),
      'router' => array(
          'routes' => array(
            'ConfigLoader' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/config',

                    'defaults' => array(
                        'controller' => 'ConfigLoader\Controller\ConfigLoader',
                        'action' => 'index',
                    ),
                ),
            ),

        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'config-loader' => __DIR__ . '/../view',
        ),

    ),

);
