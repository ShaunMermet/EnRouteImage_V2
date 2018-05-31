<?php

/**
 * Environment configuration file.  Recursively merged in over the base default.php configuration file.
 */
 
return [               
    'site' => [
        'analytics' => [
            'google' => [
                'enabled' => true
            ]
        ],
        'debug' => [
            'ajax' => false,
            'info' => false
        ]
    ]
];
