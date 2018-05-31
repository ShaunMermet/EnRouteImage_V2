<?php

/**
 * Test configuration file for UserFrosting.
 *
 */

return [
    'debug' => [
        'auth' => true
    ],
    'site' => [
        'login' => [
            'enable_email' => true
        ],
        'registration' => [
            'enabled' => true,
            'captcha' => true,
            'require_email_verification' => true,
            'user_defaults' => [
                'locale' => 'en_US',
                'group' => 'terran',
                // Default roles for newly registered users
                'roles' => [
                    'user' => true
                ]
            ]
        ]
    ]
];
