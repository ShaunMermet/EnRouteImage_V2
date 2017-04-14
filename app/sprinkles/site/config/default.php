<?php

    /**
     * Sample site configuration file for UserFrosting.  You should definitely set these values!
     *
     */
    return [
        'address_book' => [
            'admin' => [
                'name'  => 'Labelligent'
            ]
        ],    
        'debug' => [
            'auth' => true
        ],
        'site' => [
            'author'    =>      'Labelligent',
            'title'     =>      'Labelligent',
            // URLs
            'uri' => [
                'author' => 'http://enroute1.com/'
            ],
            'registration' => [
                'user_defaults' => [
                    'group' => 'groupuser'
                ]
            ]
        ],   
        'timezone' => 'Asia/Tokyo'        
    ];