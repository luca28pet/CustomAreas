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

	public function onPlace(BlockPlaceEvent $event) : void{
		if(!$event->getPlayer()->hasPermission('customareas.bypass')){
			foreach($this->plugin->areas as $area){
				if($area->isInside($event->getBlock()) && !$area->canBuild($event->getPlayer())){
					$event->setCancelled();
					$event->getPlayer()->sendMessage(str_replace('{owner}', $area->owner, $this->plugin->getConfig()->get('notice')));
				}
			}
		}
	}

	public function onBreak(BlockBreakEvent $event) : void{
		if(!$event->getPlayer()->hasPermission('customareas.bypass')){
			foreach($this->plugin->areas as $area){
				if($area->isInside($event->getBlock()) && !$area->canBuild($event->getPlayer())){
					$event->setCancelled();
					$event->getPlayer()->sendMessage(str_replace('{owner}', $area->owner, $this->plugin->getConfig()->get('notice')));
				}
			}
		}
	}

	public function onInteract(PlayerInteractEvent $event) : void{
		if(!$event->getPlayer()->hasPermission('customareas.bypass')){
			foreach($this->plugin->areas as $area){
				if($area->isInside($event->getBlock()) && !$area->canBuild($event->getPlayer())){
					$event->setCancelled();
					$event->getPlayer()->sendMessage(str_replace('{owner}', $area->owner, $this->plugin->getConfig()->get('notice')));
				}
			}
		}
	}

}