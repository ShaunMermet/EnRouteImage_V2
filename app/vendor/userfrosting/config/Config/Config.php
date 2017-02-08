<?php

/**
 * Config
 *
 * Flexible configuration class, which can load and merge config settings from multiple files and sources.
 *
 * @package   userfrosting/config
 * @link      https://github.com/userfrosting/config
 * @author    Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 * @link      http://blog.madewithlove.be/post/illuminate-config-v5/
 */
namespace UserFrosting\Config;

use Illuminate\Config\Repository;
use UserFrosting\Support\Exception\FileNotFoundException;

class Config extends Repository
{

    /**
     * @var string[] an array of paths to search for config files.
     */
    protected $paths = [];

    /**
     * Add a path to search for configuration files.
     *
     * @param string $path
     */
    public function addPath($path)
    {
        if (!is_dir($path)) {
            throw new FileNotFoundException("The config path '$path' could not be found or is not readable.");
        }

        $this->paths[] = $path;
    }

    /**
     * Set an array of paths to search for configuration files.
     *
     * @param string[] $paths
     */
    public function setPaths(array $paths = [])
    {
        $this->paths = $paths;
        return $this;
    }

    /**
     * Return a list of all paths to search for configuration files.
     *
     * @return string[]
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Recursively merge configuration values (scalar or array) into this repository.
     *
     * If no key is specified, the items will be merged in starting from the top level of the array.
     * If a key IS specified, items will be merged into that key.
     * Nested keys may be specified using dot syntax.
     * @param string|null $key
     * @param mixed $items
     */
    public function mergeItems($key = null, $items)
    {
        $targetValues = array_get($this->items, $key);
        if (is_array($targetValues)) {
            $modifiedValues = array_replace_recursive($targetValues, $items);
        } else {
            $modifiedValues = $items;
        }

        array_set($this->items, $key, $modifiedValues);
        return $this;
    }

    /**
     * Recursively merge a configuration file into this repository.
     *
     * @param string $fileWithPath
     */
    public function mergeConfigFile($fileWithPath)
    {
        if (file_exists($fileWithPath)) {
            if (!is_readable($fileWithPath)) {
                throw new FileNotFoundException("The config file '$fileWithPath' exists, but it could not be read.");
            }

            // Use null key to merge the entire configuration array
            $this->mergeItems(null, require $fileWithPath);
        }
        return $this;
    }

    /**
     * Load the configuration items from all of the files.
     *
     * @param string|null $environment
     */
    public function loadConfigurationFiles($environment = null)
    {
        // Search each config path for default and environment-specific config files
        foreach ($this->paths as $path) {
            // Merge in default config file
            $defaultFile = $this->getConfigurationFile($path);
            $this->mergeConfigFile($defaultFile);

            // Then, merge in environment-specific configuration file, if it exists
            $envFile = $this->getConfigurationFile($path, $environment);
            $this->mergeConfigFile($envFile);
        }
    }

    /**
     * Get full path of a configuration file from a specific path and environment
     *
     * @param string $path
     * @param string|null $environment
     *
     * @return string
     */
    protected function getConfigurationFile($path, $environment = null)
    {

        // If an environment is specified, load the corresponding environment file
        if ($environment) {
            $filename = $environment . '.php';
        } else {
            $filename = 'default.php';
        }

        // Allows paths with or without trailing slash, in both *nix and Windows
        $fileWithPath = rtrim($path, '/\\') . '/' . $filename;

        return $fileWithPath;
    }
}
