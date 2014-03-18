<?php 
// Bootstrap
    require_once __DIR__.'/vendor/autoload.php';

    $app = Indigo\Core\Bootstrapper::bootstrap();

// Load content
    Indigo\Controllers\ContentController::mapRoutes($app);

// Admin Silex
    if($app['config']['control_panel']) :
        $app->get('/login', 'Indigo\Controllers\AdminController::login');
        $app->get('/admin', 'Indigo\Controllers\AdminController::showDashboard');
        $app->get('/admin/account', 'Indigo\Controllers\AdminUsersController::accountPage');
        // Settings
            $app->match('/admin/settings', 'Indigo\Controllers\AdminSettingsController::settingsPage');
            $app->match('/admin/settings/clear-cache', 'Indigo\Controllers\AdminSettingsController::clearCachePage');
        // Users
            $app->get('/admin/users', 'Indigo\Controllers\AdminUsersController::showUsersPage');
            $app->match('/admin/users/add-user', 'Indigo\Controllers\AdminUsersController::addUserPage');
            $app->match('/admin/users/{id}', 'Indigo\Controllers\AdminUsersController::userEditPage')
                ->assert('id', '\d+');
            $app->match('/admin/users/{id}/delete', 'Indigo\Controllers\AdminUsersController::userDeletePage');
        // Content
            $app->get('/admin/content', 'Indigo\Controllers\AdminContentController::showContentPage');
            $app->get('/admin/node/{id}', 'Indigo\Controllers\AdminContentController::nodeIdPage');
            $app->match('/admin/content/add-content', 'Indigo\Controllers\AdminContentController::addContentPage');
            $app->get('/admin/content/{type}s', 'Indigo\Controllers\AdminContentController::showContentTypePage');
            $app->match('/admin/content/{type}s/{id}', 'Indigo\Controllers\AdminContentController::contentEditPage')
                ->assert('id', '\d+');
            $app->match('/admin/content/{type}s/{id}/delete', 'Indigo\Controllers\AdminContentController::contentDeletePage');

    endif;
    
// Admin Ember 
/*
    $app->get('/admin',  function () use ($app) {
        return $app['twig']->render("admin.twig");
    })->before('Indigo\Controllers\UserController::authenticateUser');    
*/


// API
    // GET
    $app->get('/api', 'Indigo\Controllers\ApiController::getAll')
        ->before('Indigo\Controllers\ApiController::apiAuthenticate');
    $app->get('/api/{type}s', 'Indigo\Controllers\ApiController::getNodesByType')
        ->before('Indigo\Controllers\ApiController::apiAuthenticate');
    $app->get('/api/{type}s/{id}', 'Indigo\Controllers\ApiController::getNodeByType')
        ->before('Indigo\Controllers\ApiController::apiAuthenticate');
    //POST
    $app->post('/api/{type}s', 'Indigo\Controllers\ApiController::createNodesByType')
        ->before('Indigo\Controllers\ApiController::apiAuthenticate');

    // PUT
    $app->put('/api/{type}s/{id}', 'Indigo\Controllers\ApiController::saveNodeByType')
        ->before('Indigo\Controllers\ApiController::apiAuthenticate');
    
// Error handling
    $app->error(function (\Exception $e, $code) use ($app) {
        
        // Hack to set admin template on admin page error
            $admin_template = '';

            if(substr($_SERVER['REQUEST_URI'], 0, strlen('/admin') ) === '/admin') :
                $admin_template = '@admin/';
            endif;

        switch ($code):
            case 404:
                $message = '<p>The requested page could not be found.</p>';
                break;
            default:
                $message = '<p>We are sorry, but something went wrong.</p>';
        endswitch;

        if($app['config']['debug']):
            $message .= "<pre><code>{$e}</code></pre>";
        endif;

        return $app['twig']->render("{$admin_template}error.twig", array(
            'window_title' => "{$app['config']['site_title']} | {$code}",
            'title' => $code,
            'body' => $message
        ));
    });

$app->run();