<?php

//no direct accees
defined('_JEXEC') or die('resticted aceess');

jimport('joomla.plugin.plugin');
jimport('joomla.event.plugin');

class plgSystemCache_ap extends JPlugin {

    function onAfterInitialise() {

        $app = JFACTORY::getApplication();

        if ($app->isAdmin() || strpos($_SERVER["PHP_SELF"], "index.php") === false) {
            return;
        }

        require_once __DIR__ . DS . 'cache_ap_core.php';

        $cache_ap_core = new cache_ap_core();
    }

}
