<?php

/**
 * Default configuration file for your project, which can be overridden by environment-specific configuration files.
 *
 * For example, you can override values in this config file by creating your own `development.php` config file in this same directory.
 */
 
return [      
    'site' => [
        'AdminLTE' =>  [
            'skin' => "blue"
        ],
        'analytics' => [
            'google' => [
                'code' => '',
                'enabled' => false
            ]
        ],
        'author'    => 'Author',
        'csrf'      => null,
        'debug'     => [
            'ajax' => false,
            'info' => true
        ],
        'locales' =>  [
            'available' => [
                'en_US' => 'English',
                'ar'    => 'العربية',
                'fr_FR' => 'Français',
                'pt_PT' => 'Português',
                'de_DE' => 'Deutsch',
                'th_TH' => 'ภาษาไทย'
            ],
            // This can be a comma-separated list, to load multiple fallback locales
            'default' => 'en_US'
        ],
        'title'     =>      'UserFrosting',
        // URLs
        'uri' => [
            'base' => [
                'host'              => 'localhost',
                'scheme'            => 'http',
                'port'              => null,
                'path'              => 'myProject'
            ],
            'author'            => 'http://www.userfrosting.com',
            'publisher'         => ''
        ]
    ],
    'timezone' => 'America/New_York'
];
    