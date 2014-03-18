<?php
namespace Indigo\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AdminController
{
    public function login(Request $request, Application $app)
    {
        $page = array(
            'title' => 'Login',
            'template' => 'login.twig',
            'body' => ''
        );
        
        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";
        $page['error'] = $app['security.last_error']($request);
        $page['last_username'] = $app['session']->get('_security.last_username');

        return $app['twig']->render("@admin/{$page['template']}", $page);        
    }

    public function showDashboard(Request $request, Application $app)
    {
        $page = array(
            'title' => 'Dashboard',
            'template' => 'dashboard.twig',
            'body' => 'Welcome.',
            'breadcrumbs' => \Indigo\Core\Api::getBreadCrumbs($request)
        );
        
        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";

        return $app['twig']->render("@admin/{$page['template']}", $page);
    }
}