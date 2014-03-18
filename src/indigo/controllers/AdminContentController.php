<?php
namespace Indigo\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
//use Symfony\Component\Security\Core\Validator\Constraints as Assert;
use Indigo\Controllers\ContentController;
use Indigo\Controllers\AdminUsersController;

class AdminContentController
{
    public function nodeIdPage(Request $request, Application $app, $id) 
    {
        $type = $app['content_manifest'][$id]['type'];
        $route = "/admin/content/{$type}s/{$id}";

        return $app->redirect($route);
    }

    public function showContentPage(Request $request, Application $app)
    {
        $page = array(
            'title' => 'Content',
            'template' => 'content.twig',
            'body' => '',
            'breadcrumbs' => \Indigo\Core\Api::getBreadCrumbs($request)
        );
        
        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";

        return $app['twig']->render("@admin/{$page['template']}", $page);
    }

    public function addContentPage(Request $request, Application $app)
    {
        $page = array(
            'title' => 'Add Content',
            'template' => 'add-entity.twig',
            'body' => '',
            'breadcrumbs' => \Indigo\Core\Api::getBreadCrumbs($request),
            'cancel_uri' => '/admin/content'
        );

        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";
        
        $id = count($app['content_manifest']) + 1;

        $finder = new Finder();
        $finder->files()
            ->in("themes/{$app['config']['front_end_theme']}")
            ->name('*.twig')
            ->notName('base.twig')
            ->depth('== 0');

        $templates = array();
        foreach ($finder as $template) :
            $templates[$template->getRelativePathname()] = $template->getRelativePathname();
        endforeach;

        $date = new \DateTime('now');
        $current_user = AdminUsersController::getCurrentUserInfo($app);
        $users = $app['users'];
        $select_list_users = array();

        foreach ($users as $uid => $user) :
            $select_list_users[$uid] = $user['username'];
        endforeach;

        $data = array(
            'id' => $id,
            'active' => true,
            'owner' => $current_user['id'],
            'last_updated_by' => $current_user['id'],
            'created_date' => $date,
            'last_updated_on' => $date
        );

        $form = self::getContentForm($app, $data, $templates, $select_list_users);

        $form->add('type', 'choice', array(
            'choices' => array(
                'page' => 'page', 
                'post' => 'post', 
                'archive' => 'archive'
            )
        ))
        ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) :
            $file_system = new Filesystem();

            $node = $form->getData();

            $node['route'] = \Indigo\Core\Api::sanitizeRoute($node['route']);

            $node['created_date'] = $node['created_date']->format('Y-m-d');
            $node['last_updated_on'] = $node['last_updated_on']->format('Y-m-d');

            // Create cache version of page
            if($node['type'] !== 'archive' &&
                $app['config']['cache']) :

                $rendered_node = $app['twig']->render("{$node['template']}", $node);
                $file_system->dumpFile("store/cache/{$id}.html", $rendered_node);
            endif;

            unset($node['id']);
            $body = array_pop($node);

            $content_manifest = $app['content_manifest'];
            $content_manifest[$id] = $node;

            $file_system->dumpFile("store/content/manifest.yaml", Yaml::dump($content_manifest));
            $file_system->dumpFile("store/content/{$id}.html", $body);

            return $app->redirect("/admin/content/{$node['type']}s");
        endif;

        $page['form'] = $form->createView();

        return $app['twig']->render("@admin/{$page['template']}", $page);
    }

    public function showContentTypePage(Request $request, Application $app, $type)
    {
        $title = ucwords($type);

        $page = array(
            'title' => "{$title}s",
            'template' => 'content-type.twig',
            'body' => "",
            'breadcrumbs' => \Indigo\Core\Api::getBreadCrumbs($request)
        );

        $page['type'] = $type;

        // Only return the nodes of a the specified type
            $page['content_manifest'] = array_filter($app['content_manifest'], function ($node) use ($type) {
               return $node['type'] === $type;
            });

        // Set the node ID for use in the template
            foreach ($page['content_manifest'] as $key => $node) :
                $page['content_manifest'][$key]['id'] = $key;
                $page['content_manifest'][$key]['summary'] = contentController::getSummaryText(
                    $app, file_get_contents("store/content/{$key}.html")
                );             
            endforeach;

        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";

        return $app['twig']->render("@admin/{$page['template']}", $page);
    }

    public function contentEditPage(Request $request, Application $app, $type, $id)
    {
        $page = array(
            'title' => "Edit Node {$id}",
            'template' => 'entity-edit.twig',
            'body' => '',
            'breadcrumbs' => \Indigo\Core\Api::getBreadCrumbs($request),
            'delete_uri' => "/admin/content/{$type}s/{$id}/delete",
            'cancel_uri' => "/admin/content/{$type}s"
        );

        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";

        $page['node'] = $app['content_manifest'][$id];
        $page['node']['id'] = $id;
        $page['node']['body'] = file_get_contents("store/content/{$id}.html");

        $page['footer'] = "<a class='button radius' href='{$page['node']['route']}'>View Page</a>";

        $finder = new Finder();
        $finder->files()
            ->in("themes/{$app['config']['front_end_theme']}")
            ->name('*.twig')
            ->notName('base.twig')
            ->depth('== 0');

        $templates = array();
        foreach ($finder as $template) :
            $templates[$template->getRelativePathname()] = $template->getRelativePathname();
        endforeach;

        $page['node']['created_date'] = new \DateTime($page['node']['created_date']);
        $page['node']['last_updated_on'] = new \DateTime('now');
        $current_user = AdminUsersController::getCurrentUserInfo($app);
        $users = $app['users'];
        $select_list_users = array();

        foreach ($users as $uid => $user) :
            $select_list_users[$uid] = $user['username'];
        endforeach;

        $data = array(
            'id' => $id,
            'owner' => $page['node']['owner'],
            'last_updated_by' => $current_user['id'],
            'created_date' => $page['node']['created_date'],
            'last_updated_on' => $page['node']['last_updated_on'],
            'type' => $page['node']['type'],
            'title' => $page['node']['title'],
            'route' => $page['node']['route'],
            'template' => $page['node']['template'],
            'active' => $page['node']['active'],
            'body' => $page['node']['body']
        );

        $form = self::getContentForm($app, $data, $templates, $select_list_users);

        

        if ($form->isValid()) :
            $file_system = new Filesystem();

            $node = $form->getData();

            $node['route'] = \Indigo\Core\Api::sanitizeRoute($node['route']);

            $node['created_date'] = $node['created_date']->format('Y-m-d');
            $node['last_updated_on'] = $node['last_updated_on']->format('Y-m-d');

            // Create cache version of page
            if($node['type'] !== 'archive' &&
                $app['config']['cache']) :

                $rendered_node = $app['twig']->render("{$node['template']}", $node);
                $file_system->dumpFile("store/cache/{$id}.html", $rendered_node);
            endif;

            unset($node['id']);
            $body = array_pop($node);

            $content_manifest = $app['content_manifest'];
            $content_manifest[$id] = $node;

            $file_system->dumpFile("store/content/manifest.yaml", Yaml::dump($content_manifest));
            $file_system->dumpFile("store/content/{$id}.html", $body);

            return $app->redirect("/admin/content/{$type}s");
        endif;

        $page['form'] = $form->createView();

        return $app['twig']->render("@admin/{$page['template']}", $page);
    }

    public function contentDeletePage(Request $request, Application $app, $type, $id)
    {
        $page = array(
            'title' => "Delete Node {$id}",
            'template' => 'entity-delete.twig',
            'breadcrumbs' => \Indigo\Core\Api::getBreadCrumbs($request),
            'cancel_uri' => "/admin/content/{$type}s/{$id}"
        );

        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";

        $page['node'] = $app['content_manifest'][$id];
        $page['node']['id'] = $id;

        $page['body'] = "Are you sure you want to delete {$page['node']['title']} (ID: $id)?";

        if ($request->getMethod() === 'POST') :
            $file_system = new Filesystem();
        
            $file_system->remove(array("store/cache/{$id}.html"));

            $content_manifest = $app['content_manifest'];

            unset($content_manifest[$id]);

            $file_system->dumpFile("store/content/manifest.yaml", Yaml::dump($content_manifest));
            $file_system->remove(array("store/content/{$id}.html"));

            return $app->redirect("/admin/content/{$type}s");
        endif;


        return $app['twig']->render("@admin/{$page['template']}", $page);
    }

    public static function getContentForm($app, $data, $templates, $select_list_users) 
    {
        $form = $app['form.factory']->createBuilder('form', $data)
            ->add('id', 'integer', array(
                'read_only' => true,
                'disabled' => true
            ))
            ->add('owner', 'choice', array(
                'choices' => $select_list_users
            ))
            ->add('last_updated_by', 'choice', array(
                'choices' => $select_list_users,
                'read_only' => true,
                'disabled' => true
            ))
            ->add('created_date', 'date', array(
                'read_only' => true,
                'disabled' => true
            ))
            ->add('last_updated_on', 'date', array(
                'read_only' => true,
                'disabled' => true
            ))
            ->add('title')
            ->add('route')
            ->add('template', 'choice', array(
                'choices' => $templates,
            ))
            ->add('active', 'checkbox', array(
                'label' => 'Acive (only active content will be publically displayed)?',
                'required' => false,
            ))
            ->add('body', 'textarea');
            
        return $form;
    }
    
}