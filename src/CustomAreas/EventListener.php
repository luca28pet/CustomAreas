<?php

namespace CustomAreas;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

class EventListener implements Listener{

    private $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onPlace(BlockPlaceEvent $event){
        $p = $event->getPlayer();
        if(!$p->hasPermission("customareas.bypass")){
            foreach($this->plugin->areas as $area){
                if($area->isInside(strtolower($p->getLevel()->getName()), $event->getBlock()) and !$area->canBuild($p)){
                    $event->setCancelled();
                    $p->sendMessage("This is ".$area->owner."\'s private area");
                }
            }
        }
    }

    public function onBreak(BlockBreakEvent $event){
        $p = $event->getPlayer();
        if(!$p->hasPermission("customareas.bypass")){
            foreach($this->plugin->areas as $area){
                if($area->isInside(strtolower($p->getLevel()->getName()), $event->getBlock()) and !$area->canBuild($p)){
                    $event->setCancelled();
                    $p->sendMessage("This is ".$area->owner."\'s private area");
                }
            }
        }
    }

    public function onInteract(PlayerInteractEvent $event){
        $p = $event->getPlayer();
        if(!$p->hasPermission("customareas.bypass")){
            foreach($this->plugin->areas as $area){
                if($area->isInside(strtolower($p->getLevel()->getName()), $event->getBlock()) and !$area->canBuild($p)){
                    $event->setCancelled();
                    $p->sendMessage("This is ".$area->owner."\'s private area");
                }
            }
        }
    }

}