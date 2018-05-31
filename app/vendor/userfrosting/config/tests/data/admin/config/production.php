<?php

/**
 * Test configuration file for UserFrosting.
 *
 */

return [
    'debug' => [
        'auth' => false
    ],
    'site' => [
        'login' => [
            'enable_email' => false
        ],
        'registration' => [
            'enabled' => false,
            'captcha' => false,
        ]
    ]
];
