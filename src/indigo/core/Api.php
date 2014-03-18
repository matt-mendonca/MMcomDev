<?php 
namespace Indigo\Core;

class Api
{
    public static function sanitizeRoute($route) 
    {
        $route = strtolower($route);
        $route = str_replace(' ', '-', $route);
        $route = trim($route);

        // Add a / to the front of the route if it isn't there
            $route = $route[0] === '/' ? $route : "/{$route}";
        // Remove any trailing slashes
            $route = strlen($route) > 1 ? rtrim($route, '/') : $route;

        return $route;
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
            endif;

            $breadcrumb_piece .=  "/{$uri_piece}";

            if($key === 1) :
                $breadcrumbs[$breadcrumb_piece] = 'Dashboard';
            elseif($key === count($request_uri) - 1) :
                break;
            else : 
                $breadcrumbs[$breadcrumb_piece] = ucwords($uri_piece);
            endif;
        endforeach;

        return $breadcrumbs;
    }
}