<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/i18n
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */
namespace UserFrosting\I18n;

use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use UserFrosting\Support\Repository\PathBuilder\PathBuilder;

/**
 * Globs together all files in specified locale(s) in each search path.
 *
 * @author Alexander Weissman (https://alexanderweissman.com)
 */
class LocalePathBuilder extends PathBuilder
{
    /**
     * @var string[]
     */
    protected $locales;

    /**
     * Create the loader.
     *
     * @param RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator $locator
     * @param string $uri
     * @param string|string[] $locales A list of locale names (e.g. 'en_US')
     */
    public function __construct($locator, $uri, $locales = [])
    {
        $this->setLocales($locales);

        parent::__construct($locator, $uri);
    }

    /**
     * Glob together all translation files for the current locales.
     *
     * @return array
     */
    public function buildPaths()
    {
        // Get all paths from the locator that match the uri.
        // Put them in reverse order to allow later files to override earlier files.
        $searchPaths = array_reverse($this->locator->findResources($this->uri, true, true));

        $filePaths = [];

        foreach ($this->locales as $locale) {
            // Make sure it's a valid string before loading
            if (is_string($locale) && $locale != "") {
                $localePaths = $this->buildLocalePaths($searchPaths, trim($locale));
                $filePaths = array_merge($filePaths, $localePaths);
            }
        }

        return $filePaths;
    }

    /**
     * @param string|string[] $locales
     */
    public function addLocales($locales = [])
    {
        //So we can accept strings argument also
        if (!is_array($locales)) {
            $locales = array($locales);
        }

        // Add the new locales to the end
        $this->locales = array_merge($this->locales, $locales);

        // Remove any duplicates, preserving only the last instance
        $this->locales = array_reverse(array_unique(array_reverse($this->locales)));

        return $this;
    }

    /**
     * @return string[]
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * @param string|string[] $locales
     */
    public function setLocales($locales = [])
    {
        $this->locales = [];
        $this->addLocales($locales);
        return $this;
    }

    /**
     * Construct paths to all locale files for a given locale.
     *
     * @param string[] $searchPaths
     * @param string $locale
     */
    protected function buildLocalePaths($searchPaths, $locale)
    {
        $filePaths = [];

        // Search all paths for the specified locale
        foreach ($searchPaths as $path) {
            $localePath = rtrim($path, '/\\') . '/' . $locale;
            // Grab all php files in the locale directory
            $globs = glob($localePath . "/*.php");
            $filePaths = array_merge($filePaths, $globs);
        }

        return $filePaths;
    }
}
