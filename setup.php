<?php

// Version of the plugin
define('PLUGIN_ENTITYCATEGORY_VERSION', "1.4.0RC3");
define('PLUGIN_ENTITYCATEGORY_GLPI_MIN_VERSION', '9.4');
define('PLUGIN_ENTITYCATEGORY_NAMESPACE', 'entitycategory');
// Maximum GLPI version, exclusive
define("PLUGIN_ENTITYCATEGORY_GLPI_MAX_VERSION", "9.6");

if (!defined("PLUGIN_ENTITYCATEGORY_DIR")) {
    define("PLUGIN_ENTITYCATEGORY_DIR", Plugin::getPhpDir("entitycategory"));
}
if (!defined("PLUGIN_ENTITYCATEGORY_WEB_DIR")) {
    define("PLUGIN_ENTITYCATEGORY_WEB_DIR", Plugin::getWebDir("entitycategory"));
}


/**
 * Plugin description
 *
 * @return boolean
 */
function plugin_version_entitycategory()
{
    return [
      'name' => 'EntityCategory',
      'version' => PLUGIN_ENTITYCATEGORY_VERSION,
      'author' => '<a href="https://www.probesys.com">PROBESYS</a>',
      'homepage' => 'https://github.com/Probesys/glpi-plugins-entitycategory',
      'license' => 'GPLv2+',
      'minGlpiVersion' => PLUGIN_ENTITYCATEGORY_GLPI_MIN_VERSION,
    ];
}

/**
 * Initialize plugin
 *
 * @return boolean
 */
function plugin_init_entitycategory()
{
    if (Session::getLoginUserID()) {
        global $PLUGIN_HOOKS;

        $PLUGIN_HOOKS['csrf_compliant'][PLUGIN_ENTITYCATEGORY_NAMESPACE] = true;
        //$PLUGIN_HOOKS['post_show_item'][PLUGIN_ENTITYCATEGORY_NAMESPACE] = ['PluginEntitycategoryEntitycategory', 'post_show_item'];
        $PLUGIN_HOOKS['post_item_form'][PLUGIN_ENTITYCATEGORY_NAMESPACE] = ['PluginEntitycategoryEntitycategory', 'post_item_form'];
        $PLUGIN_HOOKS['pre_item_update'][PLUGIN_ENTITYCATEGORY_NAMESPACE] = [
          'Entity' => 'plugin_entitycategory_entity_update',
        ];
    }
}

/**
 * Check plugin's prerequisites before installation
 */
function plugin_entitycategory_check_prerequisites()
{
    if (version_compare(GLPI_VERSION, PLUGIN_ENTITYCATEGORY_GLPI_MIN_VERSION, 'lt') || version_compare(GLPI_VERSION, PLUGIN_ENTITYCATEGORY_GLPI_MAX_VERSION, 'ge')) {
        echo __('This plugin requires GLPI >= ' . PLUGIN_ENTITYCATEGORY_GLPI_MIN_VERSION . ' and GLPI < ' . PLUGIN_ENTITYCATEGORY_GLPI_MAX_VERSION . '<br>');
    } else {
        return true;
    }
    return false;
}

/**
 * Check if config is compatible with plugin
 *
 * @return boolean
 */
function plugin_entitycategory_check_config()
{
    // nothing to do
    return true;
}
