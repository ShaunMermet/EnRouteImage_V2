<?php

use PHPUnit\Framework\TestCase;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use UserFrosting\Config\ConfigPathBuilder;
use UserFrosting\Support\Repository\Loader\ArrayFileLoader;

class ConfigTest extends TestCase
{
    protected $basePath;

    protected $locator;

    public function setUp()
    {
        $this->basePath = __DIR__ . '/data';
        $this->locator = new UniformResourceLocator($this->basePath);

        // Add them one at a time to simulate how they are added in SprinkleManager
        $this->locator->addPath('config', '', 'core/config');
        $this->locator->addPath('config', '', 'account/config');
        $this->locator->addPath('config', '', 'admin/config');
    }

    public function testConfigDefault()
    {
        // Arrange
        $builder = new ConfigPathBuilder($this->locator, 'config://');
        $loader = new ArrayFileLoader($builder->buildPaths());

        // Act
        $data = $loader->load();

        $this->assertEquals($data, [
            'site' => [
                'AdminLTE' => [
                    'skin' => 'blue'
                ],
                'analytics' => [
                    'google' => [
                        'code' => '',
                        'enabled' => false
                    ]
                ],
                'author' => 'Author',
                'csrf' => null,
                'debug' => [
                    'ajax' => false,
                    'info' => true
                ],
                'locales' => [
                    'available' => [
                        'en_US' => 'English',
                        'ar' => 'العربية',
                        'fr_FR' => 'Français',
                        'pt_PT' => 'Português',
                        'de_DE' => 'Deutsch',
                        'th_TH' => 'ภาษาไทย',
                    ],
                    'default' => 'en_US'
                ],
                'title' => 'UserFrosting',
                'uri' => [
                    'base' => [
                        'host' => 'localhost',
                        'scheme' => 'http',
                        'port' => null,
                        'path' => 'myProject'
                    ],
                    'author' => 'http://www.userfrosting.com',
                    'publisher' => ''
                ],
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
                        'roles' => [
                            'user' => true
                        ]
                    ]
                ]
            ],
            'timezone' => 'America/New_York',
            'debug' => [
                'auth' => true
            ]
        ]);
    }

    public function testConfigEnvironmentMode()
    {
        // Arrange
        $builder = new ConfigPathBuilder($this->locator, 'config://');
        $loader = new ArrayFileLoader($builder->buildPaths('production'));

        // Act
        $data = $loader->load();

        $this->assertEquals($data, [
            'site' => [
                'AdminLTE' => [
                    'skin' => 'blue'
                ],
                'analytics' => [
                    'google' => [
                        'code' => '',
                        'enabled' => true
                    ]
                ],
                'author' => 'Author',
                'csrf' => null,
                'debug' => [
                    'ajax' => false,
                    'info' => false
                ],
                'locales' => [
                    'available' => [
                        'en_US' => 'English',
                        'ar' => 'العربية',
                        'fr_FR' => 'Français',
                        'pt_PT' => 'Português',
                        'de_DE' => 'Deutsch',
                        'th_TH' => 'ภาษาไทย',
                    ],
                    'default' => 'en_US'
                ],
                'title' => 'UserFrosting',
                'uri' => [
                    'base' => [
                        'host' => 'localhost',
                        'scheme' => 'http',
                        'port' => null,
                        'path' => 'myProject'
                    ],
                    'author' => 'http://www.userfrosting.com',
                    'publisher' => ''
                ],
                'login' => [
                    'enable_email' => false
                ],
                'registration' => [
                    'enabled' => false,
                    'captcha' => false,
                    'require_email_verification' => true,
                    'user_defaults' => [
                        'locale' => 'en_US',
                        'group' => 'terran',
                        'roles' => [
                            'user' => true
                        ]
                    ]
                ]
            ],
            'timezone' => 'America/New_York',
            'debug' => [
                'auth' => false
            ]
        ]);
    }
}
