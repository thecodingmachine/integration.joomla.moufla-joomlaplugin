<?php

/*
JRouter returns an array of variables which are then used through the API by JInput to override the actual _GET/_POST/_REQUEST variables.
If you want to completely replace these variables, this takes a bit more work. In this case you may need to call the parse method in a sandbox
to find out what would have been the results. You can then add/delete/modify those results. Finally, before returning those results to JRouter
for the site itself, you need to flag JRouter so it doesn't continue to process the results.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

define('ROUTER_MODE_SKIP_SEF', 2);
define('ROUTER_MODE_SKIP_RAW', -1);
 
class plgSystemMoufla extends JPlugin {

        // TODO test when and where it's called

    /**
    * Constructor - note in Joomla 2.5 PHP4.x is no longer supported so we can use this.
    *
    * @access      protected
    * @param       object  $subject The object to observe
    * @param       array   $config  An array that holds the plugin configuration
    */
    public function __construct(& $subject, $config) {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    /**
    * @return  void
    */
    public function onAfterInitialise() {
        $app = JFactory::getApplication();

        // Get the router
        $router = $app->getRouter();

        // Create a callback array to call the replaceRoute method of this object
        $replaceRouteCallback = array($this, 'buildRoute');
        $parseRouteCallback = array($this, 'parseRoute');

        // Attach the callback to the router
        //$router->attachBuildRule($replaceRouteCallback);
        $router->attachParseRule($parseRouteCallback);
    }

    /**
     * @param $siteRouter
     * @param $uri
     */
    public function parseRoute(&$siteRouter, &$uri) {
        echo "Je suis dans ParseRoute....";

        /*var_dump($uri->current());
        var_dump($uri->getQuery());die;*/

        $finalArray = array();
        $queries = explode('&', $uri->getQuery());
        //var_dump($queries);

        $routeFound = false;
        foreach ($queries as $value) {
            $tmp = explode('=', $value);
            if (count($tmp) > 1) {
                if (strcmp($tmp[0], "option") == 0) {
                    $routeFound = true;
                } else {
                    $finalArray[$tmp[0]] = $tmp[1];
                }
            }
        }
        if (!$routeFound) {
            $finalArray["option"] = "com_moufla";
            $finalArray["view"] = "moufla";
            $finalArray["tmpl"] = "component";
        }
        //var_dump($finalArray);exit;
        // if (on veut du Mouf)
        //$finalArray = array("option" => "com_moufla", "view" => "moufla", "id" => "42", "desc" => "yahourt", "task" => "doSomething");
        //else
        //$finalArray = array();
        return ($finalArray);
    }

    /**
     * @param $siteRouter
     * @param $uri
     */
    public function buildRoute(&$siteRouter, &$uri) {
        echo "Je suis dans BuildRoute....";
        return (array());
    }


    /**
     * @param   JRouterSite  &$router  The Joomla Site Router
     * @param   JURI         &$uri     The URI to parse
     *
     * @return  array  The array of processed URI variables
     */
    public function myBuildRoute(&$siteRouter, &$uri) {
        echo "Je suis dans myBuildRoute....";
        defined('_JEXEC') or die('Restricted access');

//            $view = JRequest::getCmd('view',null);
//            $layout	= JRequest::getCmd('layout',null);
//            $task	= JRequest::getCmd('task',null);
//
//            JRequest::setVar('view', 'someview');
//            JRequest::setVar('layout', 'default');
//            JRequest::setVar('task', 'sometask');
//
        $lang = JFactory::getLanguage();
        $lang->load('com_moufla', JPATH_ADMINISTRATOR);
//
        if (!class_exists('Moufla')) {
            //var_dump('ezgzg');exit;
            require_once ('/components/com_moufla/moufla.php');
        }
//
        $controller = new Moufla();
        $controller->sometask();
//
//            // revert system vars to previous state
//
//            if($view != null)
//            {
//                JRequest::setVar('view', $view);
//            }
//
//            if($layout != null)
//            {
//                JRequest::setVar('layout', $layout);
//            }
//
//            if($task != null)
//            {
//                JRequest::setVar('task', $task);
//            }
        /*var_dump("buildRoute");
        var_dump($siteRouter);
        var_dump($uri);
        exit;*/
    }
}