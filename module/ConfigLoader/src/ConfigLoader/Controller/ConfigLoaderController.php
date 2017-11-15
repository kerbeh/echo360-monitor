<?php

namespace ConfigLoader\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ConfigLoader\Service\ALPConfigLoader;

class ConfigLoaderController extends AbstractActionController {

    public function indexAction() {

        return new ViewModel($this->alp());
    }

    public function alp() {

        $ALPConfig = new ALPConfigLoader();

        return ["alpRooms" => var_export($ALPConfig->getRooms(), true)];
    }

}
