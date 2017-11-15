// Define the `EchoApp` module
var EchoApp = angular.module('EchoApp', []);

// Define the `EchoDevicesController` controller on the `EchoApp` module
EchoApp.controller('EchoDevicesController', function EchoDevicesController($scope, $http) {

//    Get the json list of devices from the devices controller
//    this is a smaple json file for testing wihtout the server side
//    $http.get('js/test.json').
//    setup the first load

    getThumbnails();
    var count = 0;
    var $devices = null;
    var call = "devices";

    function getThumbnails() {

        $http.get('devices?thumbnail=true').
                success(function (data, status, headers, config) {
                    //if succses then pass it to the devices scope
                    $devices = data;
                    $scope.devices = $devices;
                }).
                error(function (data, status, headers, config) {
                    console.log(status);
                    console.log(data);
                });
    }

    setInterval(function () {
        $scope.$apply(function () {
            $http.get(call).
                    success(function (data, status, headers, config) {
                        //if succses then pass it to the devices scope
  
                        //Since the thumbnails update less often than the data,
                        // merge the thumbnails array to the content array then dispaly it
                        $scope.devices = angular.merge($devices, $devices, data);
                    }).
                    error(function (data, status, headers, config) {
                         console.log(status);
                    console.log(data);
                    });
        });

        count++;

        if (count === 3) {
            getThumbnails();
            call = 'devices?thumbnail=true';
            count = 0;
        }
        call = "devices";

    }, 10000);
});


