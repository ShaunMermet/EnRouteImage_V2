<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/config
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */
namespace UserFrosting\Config;

use UserFrosting\Support\Repository\PathBuilder\PathBuilder;

/**
 * Config path builder, which builds a list of files for a given config environment.
 *
 * @author Alexander Weissman (https://alexanderweissman.com)
 * @link http://blog.madewithlove.be/post/illuminate-config-v5/
 */
class ConfigPathBuilder extends PathBuilder
{
    /**
     * Add path to default.php and environment mode file, if specified.
     *
     * @return array
     */
    public function buildPaths($environment = null)
    {
        // Get all paths from the locator that match the uri.
        // Put them in reverse order to allow later files to override earlier files.
        $searchPaths = array_reverse($this->locator->findResources($this->uri, true, true));

        $filePaths = [];
        foreach ($searchPaths as $path) {
            $cleanPath = rtrim($path, '/\\') . '/';

            $filePaths[] = $cleanPath . 'default.php';

            if ($environment) {
                $filePaths[] = $cleanPath . $environment . '.php';
            }
        }

        return $filePaths;
    }
}
