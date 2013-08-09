<?php
namespace Newscoop\CommentsBundle\EventListener;

use Newscoop\NewscoopBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    /**
     * @param ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu[getGS('Plugins')]->addChild(
        	'Comments Plugin', 
        	array('uri' => $event->getRouter()->generate('newscoop_comments_default_admin'))
        );
    }
}