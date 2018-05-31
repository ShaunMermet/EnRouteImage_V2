<?php

use PHPUnit\Framework\TestCase;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use UserFrosting\Config\ConfigPathBuilder;

class ConfigPathBuilderTest extends TestCase
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

    public function testDefault()
    {
        // Arrange
        $builder = new ConfigPathBuilder($this->locator, 'config://');

        // Act
        $paths = $builder->buildPaths();

        $this->assertEquals([
            $this->basePath . '/core/config/default.php',
            $this->basePath . '/account/config/default.php',
            $this->basePath . '/admin/config/default.php'
        ], $paths);
    }

    public function testEnvironmentMode()
    {
        // Arrange
        $builder = new ConfigPathBuilder($this->locator, 'config://');

        // Act
        $paths = $builder->buildPaths('production');

        $this->assertEquals([
            $this->basePath . '/core/config/default.php',
            $this->basePath . '/core/config/production.php',
            $this->basePath . '/account/config/default.php',
            $this->basePath . '/account/config/production.php',
            $this->basePath . '/admin/config/default.php',
            $this->basePath . '/admin/config/production.php'
        ], $paths);
    }
}
