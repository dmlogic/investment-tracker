<?php

/**
 * General Configuration
 *
 * All of your system's general configuration settings go in here.
 * You can see a list of the default settings in craft/app/etc/config/defaults/general.php
 */

return [
    'cache'         => ENV_DEVMODE,
    'devMode'       => ENV_DEVMODE,
    'environmentName'   => ENV_NAME,
    'environmentVariables' => array(
        'basePath' => ENV_PATH.'/public/',
        'baseUrl'  => CRAFT_SITE_URL,
    ),
    'omitScriptNameInUrls' => true,
    'sendPoweredByHeader'  => false,
    'siteName'             => ENV_SITENAME,
    'siteUrl'              => CRAFT_SITE_URL,
];
