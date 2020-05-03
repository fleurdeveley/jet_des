<?php
/*
Plugin Name: ModuleDes
Description: Module de lancement de dès
Author: Fleur
Version: 0.0.1
*/

include_once(plugin_dir_path(__FILE__).'/ModuleDesWidget.php');

register_activation_hook(__FILE__, ['ModuleDesWidget', 'install']);
register_uninstall_hook(__FILE__, ['ModuleDesWidget', 'uninstall']);

add_action('widgets_init', function(){
    if(is_user_logged_in()){
        register_widget('ModuleDesWidget');
    }
});

add_action('init', ['ModuleDesWidget', 'traitement']);