<?php

    /**
     * Sample site configuration file for UserFrosting.  You should definitely set these values!
     *
     */
    return [
        'address_book' => [
            'admin' => [
                'name'  => ''
            ]
        ],    
        'debug' => [
            'auth' => false,
            'queries'=> false
        ],
        'site' => [
            'author'    =>      '',
            'locales' =>  [
                // Should be ordered according to https://en.wikipedia.org/wiki/List_of_languages_by_total_number_of_speakers,
                // with the exception of English, which as the default language comes first.
                'available' => [
                    'en_US' => 'English',
                    'ar'    => '',
                    'fr_FR' => '',
                    'pt_PT' => '',
                    'de_DE' => '',
                    'ja_JP' => '日本語',
                    'th_TH' => ''
                ],
                // This can be a comma-separated list, to load multiple fallback locales
                'default' => 'en_US'
            ],
            'title'     =>      'Labelligent',
            // URLs
            'uri' => [
                'author' => ''
            ],
            'registration' => [
                'user_defaults' => [
                    'group' => 'Public'
                ]
            ],
            'csrf' => [
                'blacklist' => [
                    '^/bbox/api/upload' => [
                        'POST'
                    ]
                ]
            ] 
        ],   
        'timezone' => 'Asia/Tokyo',
        'csrf' => [
            'blacklist' => [
                '^/bbox/api/upload' => [
                    'POST'
                ]
            ]
        ]      
    ];