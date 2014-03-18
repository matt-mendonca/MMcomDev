<?php
namespace Indigo\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class AdminUsersController
{
    public function accountPage(Request $request, Application $app)
    {
        $page = array(
            'template' => 'account.twig',
            'body' => '',
            'breadcrumbs' => \Indigo\Core\Api::getBreadCrumbs($request)
        ); 

        $page['stored_user'] = self::getCurrentUserInfo($app);

        $page['title'] = $page['stored_user']['username'];
        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";

        return $app['twig']->render("@admin/{$page['template']}", $page);
    }

    public function showUsersPage(Request $request, Application $app)
    {
        $page = array(
            'title' => 'Users',
            'template' => 'users.twig',
            'body' => "",
            'breadcrumbs' => \Indigo\Core\Api::getBreadCrumbs($request)
        );

        $page['users'] = $app['users'];

        // Set the node ID for use in the template
            foreach ($page['users'] as $key => $user) :
                $page['users'][$key]['id'] = $key;
            endforeach;

        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";

        return $app['twig']->render("@admin/{$page['template']}", $page);
    }

    public function addUserPage(Request $request, Application $app)
    {
        $page = array(
            'title' => 'Add User',
            'template' => 'add-entity.twig',
            'body' => '',
            'breadcrumbs' => \Indigo\Core\Api::getBreadCrumbs($request),
            'cancel_uri' => '/admin'
        );

        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";
        
        $id = count($app['users']) + 1;

        $data = array(
            'id' => $id
        );

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add('username')
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'required' => true,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Confirm Password')
            ))
            ->add('role', 'choice', array(
                'choices' => array('ROLE_ADMIN' => 'Admin')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) :
            $user = $form->getData();

            $users = $app['users'];

            $id = $user['id'];
            unset($user['id']);

            $users[$id]['username'] = $user['username'];
            $users[$id]['role'] = $user['role'];

            $encoder = new MessageDigestPasswordEncoder();
            $password = $encoder->encodePassword($user['password'], '');
            $users[$id]['password'] = $password;

            $file_system = new Filesystem();
            $file_system->dumpFile("config/users.yaml", Yaml::dump($users));

            return $app->redirect("/admin/users");
        endif;

        $page['form'] = $form->createView();

        return $app['twig']->render("@admin/{$page['template']}", $page);
    }

    public function userEditPage(Request $request, Application $app, $id)
    {
        $page = array(
            'title' => "Edit User {$id}",
            'template' => 'entity-edit.twig',
            'body' => '',
            'breadcrumbs' => \Indigo\Core\Api::getBreadCrumbs($request),
            'delete_uri' => "/admin/users/{$id}/delete",
            'cancel_uri' => "/admin/users"
        );

        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";

        $page['user'] = $app['users'][$id];
        $page['user']['id'] = $id;

        $data = array(
            'id' => $page['user']['id'],
            'username' => $page['user']['username'],
            'role' => $page['user']['role']
        );

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add('id', 'integer', array(
                'read_only' => true,
                'disabled' => true
            ))
            ->add('username')
            ->add('new_password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'required' => false,
                'first_options'  => array('label' => 'New Password'),
                'second_options' => array('label' => 'Confirm New Password'),
            ))
            ->add('role', 'choice', array(
                'choices' => array('ROLE_ADMIN' => 'Admin')
            ))
            // NEEDS TO BE PASSWORD FOR USER NOT LOGGED IN USER
            ->add('current_password', 'password', array(
                'constraints' => new Assert\UserPassword()
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) :
            $user = $form->getData();

            $users = $app['users'];

            $id = $user['id'];
            unset($user['id']);

            $users[$id]['username'] = $user['username'];
            $users[$id]['role'] = $user['role'];

            if(!empty($user['new_password'])) :
                $encoder = new MessageDigestPasswordEncoder();
                $password = $encoder->encodePassword($user['new_password'], '');
                $users[$id]['password'] = $password;
            endif;

            $file_system = new Filesystem();
            $file_system->dumpFile("config/users.yaml", Yaml::dump($users));

            return $app->redirect("/admin");
        endif;

        $page['form'] = $form->createView();

        return $app['twig']->render("@admin/{$page['template']}", $page);
    }

    public function userDeletePage(Request $request, Application $app, $id)
    {
        $page = array(
            'title' => "Delete User {$id}",
            'template' => 'entity-delete.twig',
            'breadcrumbs' => \Indigo\Core\Api::getBreadCrumbs($request),
            'cancel_uri' => "/admin/users/{$id}"
        );

        $page['window_title'] = "{$app['config']['site_title']} | {$page['title']}";

        $page['user'] = $app['users'][$id];
        $page['user']['id'] = $id;

        $page['body'] = "Are you sure you want to delete {$page['user']['username']} (ID: $id)?";

        if ($request->getMethod() === 'POST') :

            $users = $app['users'];

            unset($users[$id]);

            $file_system = new Filesystem();
            $file_system->dumpFile("config/users.yaml", Yaml::dump($users));

            return $app->redirect("/admin/users");
        endif;

        return $app['twig']->render("@admin/{$page['template']}", $page);
    }

    public static function getCurrentUserInfo($app) {
        $token = $app['security']->getToken();

        if (null !== $token) :
            $user = $token->getUser();
            $username = $user->getUsername();
        endif;

        // Get user Meta
            $user_object = array_filter($app['users'], function ($stored_user) use ($username) {
               return $stored_user['username'] === $username;
            });

        // Set the user ID for use in the template
            foreach ($user_object as $key => $node) :
                $user_object[$key]['id'] = $key;
            endforeach;

        return reset($user_object);
    }
}