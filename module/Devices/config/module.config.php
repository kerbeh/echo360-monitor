<?php
return array(
  'controllers' => array(
      'invokables' => array(
          'Devices\Controller\Devices' => 'Devices\Controller\DevicesController',
          'Devices\Controller\DeviceDisplay' => 'Devices\Controller\DeviceDisplayController',

      ),
  ),
      'router' => array(
          'routes' => array(
            'Devices' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/devices',

                    'defaults' => array(
                        'controller' => 'Devices\Controller\Devices',
                        'action' => 'devices',
                    ),
                ),
            ),
            'DeviceDisplay' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/wall',
                    'defaults' => array(
                        'controller' => 'Devices\Controller\Devices',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'devices' => __DIR__ . '/../view',
        ),
        //comment this out to echo stuff straight from the controller TODO
        'strategies' => array(
            'ViewJsonStrategy'
        ),
    ),

);
