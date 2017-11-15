<?php

namespace ConfigLoader\Service;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Config\Config;
use ZendOAuth;
use Zend\Http\Client;
use League\OAuth2\Client\Provider\GenericProvider;

class ALPConfigLoader {

    /**
     * var to hold a Zend Config object with the loaded autoload/local.php file
     * @var Zend Config 
     */
    private $config;

    /**
     * Array of rooms and IPs with IP as the keys.
     * @var Array 
     */
    private $rooms = array();

    /**
     * Constructor that loads the config file into the config object
     * and calls the room and IP functions
     */
    function __construct() {

        $this->config = new Config(include 'config/autoload/local.php');

     
        
        //TODO build a proper exception for blank credentials
        if ($this->config->alpCredentials->clientId == "") {
            $this->setRooms(["Error" => "Credentials not set"]);
        } else {

            $this->setRooms($this->getRoomIP($this->getroomIds()));
        }
    }

    /**
     * Getter for the rooms array
     * @return Array
     */
    public function getRooms() {
        return $this->rooms;
    }

    /**
     * Setter for the room array
     * @param type $rooms
     * @return $this // Array of rooms
     */
    private function setRooms($rooms) {
        $this->rooms = $rooms;
        return $this;
    }

    /**
     * Function to load the Oauth library and 
     * retrive a token based on the credentials in autoload/local.php
     * @return String //Oauth token string
     */
    private function getAccessToken() {

        $provider = new GenericProvider($this->config->alpCredentials->toArray());

        try {

            // Try to get an access token using the client credentials grant.
            $accessToken = $provider->getAccessToken('client_credentials');
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

            // Failed to get the access token
            exit($e->getMessage());
        }

        return $accessToken->getToken();
    }

    /**
     * Funciton to query the Echo ALP api for a list of rooms
     * @return Array / Array of Room names and UUIDs
     */
    private function getroomIds() {


        $client = new Client('https://echo360.org.au:443/public/api/v1/' . 'rooms?access_token=' . $this->getAccessToken());

        $client->setMethod('GET');
        $response = $client->send();

        $responseArray = json_decode($response->getBody(), true);
        $roomConfigurationIdArray = [];


        foreach ($responseArray["data"] as $key => $value) {
            if (isset($value['id'])) {
                $roomConfigurationIdArray[$value['id']] = $value['name'];
            }
        }

        return $roomConfigurationIdArray;
    }

    /**
     * Function that takes a list of Echo room 
     * UUIDs and gets the IP address for each UUID
     * @param Array $roomConfigurationIds
     * @return Array //Returns an array of room names and IPs or room names and errors.
     */
    private function getRoomIP($roomConfigurationIds) {
        $roomConfig = [];
        foreach ($roomConfigurationIds as $key => $value) {
            $client = new Client('https://echo360.org.au:443/public/api/v1/rooms/' . $key . '/network' . '?access_token=' . $this->getAccessToken());

            $client->setMethod('GET');
            $response = $client->send();

            $responseArray = json_decode($response->getBody(), true);

            //Check if the IP is provided, TODO expand this check to account for DHCP
            if (isset($responseArray['ipAddress'])) {
                $roomConfig[$responseArray['ipAddress']] = $value;
            } else {
                $roomConfig["error fetching IP for $value"] = $value;
            }
        }
        return $roomConfig;
    }

}
