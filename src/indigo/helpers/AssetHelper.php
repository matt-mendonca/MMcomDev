<?php 
namespace Indigo\Helpers;

use Assetic\Asset\AssetManager;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\AssetWriter;

class AssetHelper
{
    public static function compileJsAssets(Request $request, Application $app)
    {
        /*
        $asset_manager = new AssetManager();
        $asset_writer = new AssetWriter('/resources/js');

        $asset_manager->set('jquery', new FileAsset('/path/to/jquery.js'));

        $writer->writeAsset(
            $asset_manager->get(
                $name
            )
        );
        */
    }
}