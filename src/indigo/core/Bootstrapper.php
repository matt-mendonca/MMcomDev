<?php 
namespace Indigo\Core;

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;

class Bootstrapper
{
    public static function bootstrap()
    {
        $app = new Application();

        $app['config'] = Yaml::parse("config/config.yaml");
        $app['users'] = Yaml::parse("config/users.yaml");
        $app['content_manifest'] = Yaml::parse("store/content/manifest.yaml");
        $app['event_dispatcher'] = new EventDispatcher();

        date_default_timezone_set($app['config']['timezone']);

        $app->register(new SessionServiceProvider());
        $app->register(new FormServiceProvider());
        $app->register(new ValidatorServiceProvider());

        $app->register(new TranslationServiceProvider(), array(
            'translator.messages' => array(),
        ));

        $app->register(new TwigServiceProvider(), array(
            'twig.path' => "themes/{$app['config']['front_end_theme']}",
        ));

        // Setup twig namespace for admin theme
            $app['twig.loader.filesystem']->addPath(
                "themes/{$app['config']['admin_theme']}",
                'admin'
            );

        // Create array of users in Symfony Security firewall format
            $firewall_users = array();

            foreach ($app['users'] as $id => $user) :
                $firewall_users[$user['username']] = array(
                    $user['role'],
                    $user['password']
                );
            endforeach;
            $app['firewall_users'] = $firewall_users;

        // Setup Security Firewalls
            $app->register(new SecurityServiceProvider(), array(
                'security.firewalls' => array(
                    'admin' => array(
                        'pattern' => '^/admin',
                        'form' => array('login_path' => '/login', 'check_path' => '/admin/login_check'),
                        'logout' => array('logout_path' => '/admin/logout'),
                        'users' => $app['firewall_users']
                    )
                )
            ));

        // Debug  
            if($app['config']['debug']) :
                error_reporting(E_ALL);
                ini_set('display_errors', TRUE);
                ini_set('display_startup_errors', TRUE);
            endif;

        return $app;
    }

    public static function getBreadCrumbs($request) 
    {
        $request_uri = $request->getRequestUri();

        $request_uri = explode('/', $request_uri); 
        $breadcrumbs = array();
        $breadcrumb_piece = '';

        foreach ($request_uri as $key => $uri_piece) :
            if(empty($uri_piece) && $key === 0) :
                continue;
            elseif($key === count($request_uri) - 1) :
                break;
            endif;

            $breadcrumb_piece .=  "/{$uri_piece}";
            $breadcrumbs[$breadcrumb_piece] = ucwords($uri_piece);
        endforeach;

        return $breadcrumbs;
    }
}