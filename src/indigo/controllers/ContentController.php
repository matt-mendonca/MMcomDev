<?php
namespace Indigo\Controllers;

use Silex\Application;
use Helthe\Component\Turbolinks\Turbolinks;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;

class ContentController
{
    public static function mapRoutes($app)
    {
        $content_manifest = array_filter($app['content_manifest'], function ($node) {
            return $node['active'] === true;
        });

        foreach ($content_manifest as $id => $node):
            $app->get("/node/$id", function () use ($app, $content_manifest, $id, $node) {
                return $app->redirect($node['route']);
            });

            $app->get($node['route'], function () use ($app, $content_manifest, $id, $node) {
                $file_system = new Filesystem();
                // Serve static file if it exits
                    if( $file_system->exists("store/cache/{$id}.html") && 
                        $app['config']['cache'] ) :

                        return file_get_contents("store/cache/{$id}.html");
                    endif;

                $node['window_title'] = "{$app['config']['site_title']} | {$node['title']}";

                $node['body'] = file_get_contents("store/content/{$id}.html");

                if($node['type'] === 'archive'):
                    $node['child_nodes'] = array();

                    foreach ($content_manifest as $child_id => $child_node):
                        
                        if( $child_id === 0 ||
                            $child_id === $id ||
                            !stristr($child_node['route'], $node['route']) 
                        ):
                            continue;
                        endif;
                                                
                        $child_node['body'] = self::getSummaryText(
                            $app, file_get_contents("store/content/{$child_id}.html")
                        );

                        $node['child_nodes'][] = $child_node;
                    endforeach;
                endif;

                $response = $app['twig']->render("{$node['template']}", $node);

                if( $node['type'] !== 'archive' &&
                    $app['config']['cache'] ) :

                    $file_system->dumpFile("store/cache/{$id}.html", $response);
                endif;

                return $response;
            })
            ->after('Indigo\Controllers\contentController::turboLinksResponse');
        endforeach;
    }

    public static function getSummaryText($app, $text) 
    {
        $text = strip_tags($text);

        if(strlen($text) > $app['config']['summary_text_length'] + 1) :
            $text = preg_replace(
                '/\s+?(\S+)?$/', 
                '', 
                substr($text, 0, $app['config']['summary_text_length'])
            );
        
            $text .= " [...]";
        endif;

        return $text;
    }

    public static function turboLinksResponse(Request $request, Response $response) 
    {
        $turbolinks = new Turbolinks();
        $turbolinks->decorateResponse($request, $response);
    }
}