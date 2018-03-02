<?php

namespace Devices\Service;

use Zend\Config\Config;
use Zend\Http\Client;

class DeviceService {

    private $URLarray;
    private $thumbnails;
    private $config;

    public function __construct($URLarray, $thumbnails) {

        if (isset($URLarray) === !true && isset($thumbnails) === !true) {
            return ["error" => "URL array or thumbnail request was not supplied"];
        } else {
            $this->URLarray = $URLarray;
            $this->thumbnails = $thumbnails;
            $this->config = $config = new Config(include 'config/autoload/local.php');
        }
    }

    public function getRoom($deviceList) {

        $rooms = array_flip($this->config->rooms->toArray());

        foreach ($deviceList as $key => $value) {

            $deviceList[$key]['room'] 
                    = $rooms[$key];
        }
        return $deviceList;
    }

    public function deviceList() {    
        $deviceResponses = $this->queryDevices($this->URLarray);

        $filteredDeviceResponses = $this->filterDeviceResponses($deviceResponses);


//Loop over each of the device XML responses
        $CurrentCaptureArray = array();

        foreach ($filteredDeviceResponses['devices'] as $key => $device) {

            $temp = $this->ParseXML($device);

            $CurrentCaptureArray[$key] = $temp;
        }

        $totals = ["total_devices" => count($this->URLarray), "active_devices" => count($CurrentCaptureArray), "timedout_devices" => count($deviceResponses['errors'])];

        // Loop over the active captures and get the thumbs
        if ($this->thumbnails == true) {
            $output = $this->GetThumbnail($CurrentCaptureArray);
        } else {
            $output = $CurrentCaptureArray;
        }



        return ["totals" => $totals, "devices" => $this->getRoom($output)];
    }

    
    /*
    public function queryDevicesOld($URLarray) {

        $returnArray = [];
        $errorArray = [];

        foreach ($URLarray as $key => $value) {
         
            $uri = "$value/status/current_capture";
               print_r("$uri");
            $client = new Client;
            $client->setUri($uri);
            $client->setAuth($this->config->deviceCredentials->username, $this->config->deviceCredentials->password, $type = self::AUTH_BASIC);
            $client->setMethod('GET');
            $response = $client->send();
print_r($response);

return $response;


            if ($response->getStatusCode() == 200) {
                print_r($response->getContent());
            } else {
                //TODO error catch here
                //return  $response->getContent();
                echo "fail";
            }
        }
    }
*/
    public function queryDevices($URLarray) {

        $returnArray = array();
        $urlCount = count($URLarray);

        $curlArray = array();
        $curlMulti = curl_multi_init();

        $error_array = [];

        for ($i = 0; $i < $urlCount; $i++) {

            $url = $URLarray[$i];
            $curlArray[$i] = curl_init($url . ":8080/status/current_capture");
            curl_setopt($curlArray[$i], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlArray[$i], CURLOPT_USERPWD, $this->config->deviceCredentials->username . ":" . $this->config->deviceCredentials->password);
            curl_setopt($curlArray[$i], CURLOPT_SSL_VERIFYHOST, false); //TODO remove this later once SSL has been sorted
            curl_setopt($curlArray[$i], CURLOPT_CONNECTTIMEOUT, 1);
            curl_multi_add_handle($curlMulti, $curlArray[$i]);
            
        }

        do {
            curl_multi_exec($curlMulti, $running);
            //28 is the Curl error code for CURL_OPERATION_TIMEDOUT (28) 
            if (curl_multi_info_read($curlMulti)['result'] === 28 ) {

                $error_array[] = $URLarray[$running];
            }
          
        } while ($running > 0);



        for ($i = 0; $i < $urlCount; $i++) {
            
   

            $results = curl_multi_getcontent($curlArray[$i]);

            if ($results !== null && strpos($results, '401 Unauthorized') == false) {
                $returnArray[$URLarray[$i]] = $results;
            }else {
                //log this echo "this is an error";
            }
        }

        return ["devices" => $returnArray, "errors" => array_unique($error_array)];
    }

    private function filterDeviceResponses($deviceResponses) {

        foreach ($deviceResponses['devices'] as $key => $device) {

            $xml = simplexml_load_string($device);

            /**
             * Error handling of loading the xml and chekcing if there is an active capture is done here.
             * need to investigate logging or returning errors in the JSON
             * TODO
             */
            if ($xml === false) {
                //echo "Failed loading XML </br>";
                unset($deviceResponses['devices'][$key]);
            } elseif ($xml->current->state->__toString() == null) {
                //echo "no capture </br>";
                unset($deviceResponses['devices'][$key]);
            } elseif ($xml->current->state->__toString() !== "active") {
                //echo "no active capture </br>";
                unset($deviceResponses['devices'][$key]);
            }
        }

        return $deviceResponses;
    }

    public function ParseXML($xmlCurrentCapture) {

        $xml = simplexml_load_string($xmlCurrentCapture);

        $sources = array();

        foreach ($xml->current->sources->source as $source) {

            if ($source->class == "vga" && strpos($source->name, 'stream1') == false) {
                //TODO This is a hack for SCHD devices to hide any stream greater than 0. I.e. the live stream which is a duplicate of the physical stream. (may not work with POD devices)

                $sources[$source->class->__toString()][$source->name->__toString()]["signal-present"] = $source->{'signal-present'}->__toString();
                $sources[$source->class->__toString()][$source->name->__toString()]["thumbnail"] = $source->thumbnail->__toString();
            }
            if ($source->class == "audio") {
                $sources[$source->class->__toString()][$source->name->__toString()]["signal-present"] = $source->{'signal-present'}->__toString();

                foreach ($xml->xpath("//source/channels/channel") as $audioChannel) {

                    $sources[$source->class->__toString()][$source->name->__toString()][$audioChannel->position . "_average"] = $audioChannel->average->__toString();
                    $sources[$source->class->__toString()][$source->name->__toString()][$audioChannel->position . "_peak"] = $audioChannel->peak->__toString();
                }
            }
        }

        $sources['start-time'] = strtotime($xml->current->schedule->{"start-time"}->__toString());
        $sources['duration'] = $xml->current->schedule->duration->__toString();
        $sources['section'] = $xml->current->schedule->parameters->section->__toString();
        $sources['title'] = $xml->current->schedule->parameters->title->__toString();


        return $sources;
    }

    public function GetThumbnail($devicesArray) {

        foreach ($devicesArray as $deviceIP => $device) {

            if (isset($device['vga']) != true) {
                continue;
            }//add a check for audio only? how many capture profile?

            foreach ($device['vga'] as $graphicsChannel => $signal) {

                if ($signal["signal-present"] != TRUE) {
                    break;
                } else {
                    $client = new Client();
                    $client->setAuth($this->config->deviceCredentials->username, $this->config->deviceCredentials->password, \Zend\Http\Client::AUTH_BASIC);
                    $client->setUri("http://" . $deviceIP . ":8080/monitoring/" . $signal['thumbnail'] . "?" . time());
                    $client->setMethod(\Zend\Http\Request::METHOD_GET);

                    $response = $client->send();
                    //TODO error handing on http status code
                    $devicesArray[$deviceIP]["vga"][$graphicsChannel]["base64Thumbnail"] = base64_encode($response->getContent());
                }
            }
        }
        return $devicesArray;
    }

}
