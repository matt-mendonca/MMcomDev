<?php
namespace Indigo\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
//use Symfony\Component\Security\Core\Validator\Constraints as Assert;

class AdminSettingsController
{
    public function settingsPage(Request $request, Application $app)
    {
        $page = array(
            'title' => 'Settings',
            'template' => 'settings.twig',
            'body' => '',
            'breadcrumbs' => \Indigo\Core\Api::getBreadCrumbs($request)
        );
        
        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";

        $finder = new Finder();
        $finder->directories()->in('themes/')->depth('== 0');

        $themes = array();
        foreach ($finder as $folder) :
            $themes[$folder->getRelativePathname()] = $folder->getRelativePathname();
        endforeach;

        /* This is expsenive. Including a static file instead.
        $php_time_zones = \DateTimeZone::listIdentifiers();
        $time_zones = array();

        foreach ($php_time_zones as $key => $time_zone) :
            $time_zones[$time_zone] = str_replace('_', ' ', $time_zone);
        endforeach;
        */

        include('config/timezones.php');

        $data = array(
            'site_title' => $app['config']['site_title'],
            'summary_text_length' => $app['config']['summary_text_length'],
            'time_zone' => $app['config']['time_zone'],
            'front_end_theme' => $app['config']['front_end_theme'],
            'admin_theme' => $app['config']['admin_theme'],
            'control_panel' => $app['config']['control_panel'],
            'cache' => $app['config']['cache'],
            'debug' => $app['config']['debug']
        );

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add('site_title')
            ->add('summary_text_length', 'integer', array(
                'required' => true
            ))
            ->add('time_zone', 'choice', array(
                'choices' => $time_zones
            ))
            ->add('front_end_theme', 'choice', array(
                'choices' => $themes
            ))
            ->add('admin_theme', 'choice', array(
                'choices' => $themes
            ))
            ->add('control_panel', 'checkbox', array(
                'label' => 'Enable the Admin Control Panel? *WARNING: Disabling this will turn off the admin UI (www.{yoursite}.com/admin). If you disable this you will have to renable it by editing the config file directly.*',
                'required' => false,
            ))
            ->add('cache', 'checkbox', array(
                'label' => 'Enable Page Caching?',
                'required' => false,
            ))
            ->add('debug', 'checkbox', array(
                'label' => 'Enable Debugging Mode?',
                'required' => false,
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) :
            $data = $form->getData();

            $config = Yaml::dump($data);
            $file_system = new Filesystem();
            $file_system->dumpFile("config/config.yaml", $config);

            return $app->redirect('/admin');
        endif;

        $page['form'] = $form->createView();

        return $app['twig']->render("@admin/{$page['template']}", $page);
    }

    public function clearCachePage(Request $request, Application $app)
    {
        $page = array(
            'title' => 'Clear Cache',
            'template' => 'entity-delete.twig',
            'breadcrumbs' => \Indigo\Core\Api::getBreadCrumbs($request),
            'cancel_uri' => '/admin/settings'
        );

        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";

        $page['body'] = "Are you sure you want to clear the cache?";

        if ($request->getMethod() === 'POST') :
            $finder = new Finder();
            $finder->files()
                ->in("store/cache")
                ->name('*.html');

            $file_system = new Filesystem();
            $file_system->remove($finder);

            return $app->redirect("/admin/settings");
        endif;


        return $app['twig']->render("@admin/{$page['template']}", $page);
    }
}