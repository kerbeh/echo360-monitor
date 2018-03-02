<?php

namespace Devices\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Http;
use Zend\Config\Config;
use Devices\Service\DeviceService;

class DevicesController extends AbstractActionController {

    public function indexAction() {

        return [];
    }

    public function devicesAction() {
        //read the configuration array file
        //TODO ENCRYPT THIS
        $config = new Config(include 'config/autoload/local.php');


        //Build a parseable array from the config file
        $URLarray = array_values($config->rooms->toArray());
        //Check the uri query to see if thumbnails are requested
        $thumbnails = $this->getRequest()->getQuery('thumbnail');


        $deviceService = new DeviceService($URLarray, $thumbnails);
        $output = $deviceService->deviceList();

        $result = new JsonModel($output);

        return $result;
    }

}
