<?php
namespace Indigo\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class ApiController
{
    public function getAll(Request $request, Application $app)
    {
        $nodes = array();

        foreach ($app['content_manifest'] as $id => $node):
            $node['id'] = (int) $id;
            $node['body'] = file_get_contents("store/content/{$id}.html");
            $nodes[] = $node;
        endforeach;

        return $app->json(
            array(
                'pages' => $nodes
            )
        );
    }

    public function getNodesByType(Request $request, Application $app, $type)
    {
        $nodes = array();

        foreach ($app['content_manifest'] as $id => $node):
            if($node['type'] === $type):
                $node['id'] = (int) $id;
                $node['body'] = file_get_contents("store/content/{$id}.html");

                $nodes[] = $node;
            endif;
        endforeach;

        return $app->json(
            array(
                "{$type}" => $nodes
            )
        );
    }

    public function getNodeByType(Request $request, Application $app, $type, $id) 
    {
        $node = $app['content_manifest'][$id];

        if($node['type'] !== $type):
            return $app->json(array('page' => array()));
        endif;

        $node['id'] = (int) $id;
        $node['body'] = file_get_contents("store/content/{$id}.html");

        $nodes = array(
            "{$type}" => $node
        );
        
        return $app->json(
            array(
                "{$type}" => $node
            )
        );
    }

    public function creatNodeByType(Request $request, Application $app, $type) 
    {
        if(!$data = json_decode($request->getContent(), true)) :
            return new Response("Missing parameters", 400);
        endif;

        $content_manifest = $app['content_manifest'];

        ob_start();
            var_dump($content_manifest);
        $debug = ob_get_clean();

        return new Response($debug, 201);
    }

    public function saveNodeByType(Request $request, Application $app, $type, $id) 
    {
        if(!$data = json_decode($request->getContent(), true)) :
            return new Response("Missing parameters", 400);
        endif;

        $node = $data['post'];

        $content_manifest = $app['content_manifest'];

        $content_manifest[$id]['route'] = $node['route'];
        $content_manifest[$id]['type'] = $node['type'];
        $content_manifest[$id]['template'] = $node['template'];
        $content_manifest[$id]['title'] = $node['title'];

        $content_manifest = Yaml::dump($content_manifest);
        file_put_contents("store/content/manifest.yaml", $content_manifest);
        file_put_contents("store/content/{$id}.html", $node['body']);

        $static_node_file = $app['twig']->render("{$node['template']}", $node);
        file_put_contents("store/cache/{$id}.html", $static_node_file);

        $static_node_pjax_file = $app['twig']->render("pjax.{$node['template']}", $node);
        file_put_contents("store/cache/pjax.{$id}.html", $static_node_pjax_file);

        return new Response('Node saved.', 200);
    }

    public static function apiAuthenticate(Request $request, $app)
    {
        $user = $request->server->get('PHP_AUTH_USER', false);
        $pass = $request->server->get('PHP_AUTH_PW');

        if( $user !== 'test' ||
            $pass !== 'test') :
            //return new Response('Unauthorized', 403);
        endif;
    }
}