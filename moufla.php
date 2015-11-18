<?php

/*
JRouter returns an array of variables which are then used through the API by JInput to override the actual _GET/_POST/_REQUEST variables.
If you want to completely replace these variables, this takes a bit more work. In this case you may need to call the parse method in a sandbox
to find out what would have been the results. You can then add/delete/modify those results. Finally, before returning those results to JRouter
for the site itself, you need to flag JRouter so it doesn't continue to process the results.
*/

/* no direct access */
defined( '_JEXEC' ) or die( 'Restricted access' );

define('ROUTER_MODE_SKIP_SEF', 2);
define('ROUTER_MODE_SKIP_RAW', -1);

use Mouf\Integration\Joomla\Moufla\Moufla;

class plgSystemMoufla extends JPlugin {

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

        include_once(__DIR__."/../../../mouf/Mouf.php");
        // define the root URL here, because of a Mouf conflict
        define('ROOT_URL', JURI::root(true).'/');
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
        $router->attachBuildRule($replaceRouteCallback);
        $router->attachParseRule($parseRouteCallback);
    }

    /**
     * @param $siteRouter
     * @param $uri
     *
     * @return associative array with routing values
     */
    public function parseRoute(&$siteRouter, &$uri) {
        $finalArray = array();
        $queries = explode('&', $uri->getPath());

        // Call to vendor/mouf/integration...moufla
        $moufla = new Moufla();
        $response = $moufla->searchForRoute();

        // If the request is a normal Joomla request
        if ($response->hasHeader('Vary') && $response->getHeader('Vary')[0] == "mouflaNotFound") {
            // We create the final array for Joomla. It will call it own mods/components
            foreach ($queries as $value) {
                $tmp = explode('=', $value);
                if (count($tmp) > 1) {
                    $finalArray[$tmp[0]] = $tmp[1];
                }
            }
        // Else the request is a Mouf request
        } else {
            // Checking if the template has been called or not.
            $joomlaTemplate = Mouf::getJoomlaTemplate();

            $joomlaTemplateCalled = false;
            if (!$joomlaTemplate->getTemplateCalled()) {
                if ($response instanceof \Mouf\Mvc\Splash\HtmlResponse) {
                    $htmlElement = $response->getHtmlElement();
                    if ($htmlElement instanceof \Mouf\Integration\Joomla\Moufla\JoomlaTemplate) {
                        $joomlaTemplateCalled = true;
                    }
                }
            }


            if (!$joomlaTemplateCalled) {
                $finalArray["tmpl"] = "component"; // Will not display Joomla template
                $finalArray["mouflaJson"] = "true";
            }
            // On appelle notre composant pour ensuite afficher la vue du controlleur Mouf trouvé
            $finalArray["option"] = "com_moufla";
            $finalArray["view"] = "moufla";
            $finalArray["response"] = $response;
        }
        return ($finalArray);
    }

    /**
     * No need for Moufla
     * @param $siteRouter
     * @param $uri
     *
     * @return array associative array with routing values
     */
    public function buildRoute(&$siteRouter, &$uri) {
        return (array());
    }
}