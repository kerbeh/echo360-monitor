
# Echo360 Capture Monitor 👀
Echo360 Capture Monitor is a simple web interface to monitor the current status, volume level and on screen displays of all active capture appliances from one webpage.

![Echo360 Capture Monitor Screenshot](https://i.imgur.com/fTgrZCl.png)

Designed to be simple, read-only and separate the credentials from simply viewing the captures. Support users can view active captures without sharing the admin password or adding users to the sections.

## Setup Instructions
Echo360 Capture Monitor uses a PHP config file with a list of Capture Appliance web-addresses and room names as well as credentials.
The config file is stored in /config/config.php

### Device credentials config:
The device credentials depend on your Institutions configuration, more information on finding the device credentials can be found here:
[Echo Support Article](https://support.echo360.com/customer/en/portal/articles/2872308-common-settings---device-defaults?b_id=16609)

```
   'deviceCredentials' => array(
       'username' =>'',//The username for the capture appliance
       'password' =>''//The password for the capture appliance
    ),
```
### Room config:
Echo monitor needs to know some information about your institutions rooms and the web address of the capture applicanes in them.
These can be set in the below format, note that the port is required but the protocol is not.
```
 'rooms' => array(
       'localhost:8080' =>'TEST.01',

    )

```
There are bookmarklets to auto build the arrays.
They are avaliable for [EchoESS](https://gist.github.com/kerbeh/64fcf2bbd5717c2b0359961e3bf156ff) and [EchoALP](https://gist.github.com/kerbeh/6c199d8c409ad68529e255f5809da657)
## Installation
Echo360 Capture Monitor requires a PHP web server to run and uses composer for dependency management.
After downloading you must install dependencies using
```
composer -install
```
The simplest way to get started locally is to navigate to the "public" folder in Echo360 Capture Monitor and run:
```
php -S localhost:8080
```
Then navigate to localhost:8080 in your web-browser to see the application.

Alternatively you can deploy the application on a dedicated PHP host.

__Important!: Ensure only the public directory is served to the web, The config file contains passwords and must not be served__

## Useage Instructions
The landing page will produce a panel for each running capture.
These panels contain all the important information to check that captures are running smoothly. 

![Hello World](https://i.imgur.com/sdWhSw6.png)

## Technology
Echo360 Capture Monitor is an Angular app, styled in bootstrap with a Zend2 back end to aggregate and poll the Echo Capture Appliances API. These technologies have been chosen as a learning exercise and are not intended to be the newest or best.

## Missing Features
- Currently this is a wrapper for Echo capture appliances only. This does not interface with Echo center or Echo ALP (yet) this means that admins will need to maintain a config file of echo appliance IPs.
- No way to filter the devices. All devices will be queried and only active captures will be shown in the web interface
- As the echo devices are stored in a config file. Devices configured with DHCP will need frequent updating.
- SSL Certificates are supported but not verified
- Encryption on the config file

## Contributing
Contributions are most welcome as are issues reports.
Please be aware that this a personal project and updates may be slow.