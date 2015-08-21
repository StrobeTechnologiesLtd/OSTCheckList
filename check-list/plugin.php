<?php

/**
 * Check List Plugin
 * This plugin allows you to add a daily, monthly and yearly
 * check list of items to be done.
 *
 * @author Robin Toy <robin at strobe-it.co.uk>
 */

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__file__).'/include');
return array(
    'id' =>             'strobeit:check',
    'version' =>        '0.2.0',
    'name' =>           'Check List',
    'author' =>         'Robin Toy',
    'description' =>    'Provides the ability to create daily, monthly & yearly tasks to be performed and results recorded.',
    'url' =>            'http://www.strobe-it.co.uk/',
    'plugin' =>         'checklist.php:CheckListPlugin'
);

?>