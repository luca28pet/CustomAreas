<?php

namespace CustomAreas;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{

	private $selections = [];
	/**@var Area[]*/
	public $areas = [];

	/*
	 * array areasData[
	 *   0 => [
	 *     'pos1' => [1, 2, 3]
	 *     'pos2' => [3, 4, 5]
	 *     'level' => survival
	 *     'owner' => 'luca28pet'
	 *     'whiteList' => ['a', 'b']
	 *   ]
	 * ]
	 */

	public function onEnable() : void{
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		if(file_exists($this->getDataFolder()."areas.json")){
			$areasData = json_decode(file_get_contents($this->getDataFolder()."areas.json"), true);
			foreach($areasData as $area){
				$this->areas[] = new Area($this, $area["pos1"], $area["pos2"], $area["level"], $area["owner"], $area["whiteList"]);
			}
		}
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	public function onDisable() : void{
		$data = [];
		foreach($this->areas as $area){
			$data[] = ["pos1" => $area->min, "pos2" => $area->max, "level" => $area->level, "owner" => $area->owner, "whiteList" => $area->whiteList];
		}
		file_put_contents($this->getDataFolder()."areas.json", json_encode($data));
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(!($sender instanceof Player)){
			$sender->sendMessage("Please run this command in game");
			return true;
		}
		if(!isset($args[0])){
			$sender->sendMessage("CustomAreas plugin by luca28pet.");
			$sender->sendMessage($command->getUsage());
			return true;
		}
		switch(strtolower(array_shift($args))){
			case "pos1":
				foreach($this->areas as $area){
					if($area->isInside($sender)){
						$sender->sendMessage($this->getConfig()->get("position-conflict"));
						return true;
					}
				}
				$this->selections[$sender->getName()]["pos1"] = [$sender->getFloorX(), $sender->getFloorY(), $sender->getFloorZ(), $sender->getLevel()->getName()];
				$sender->sendMessage($this->getConfig()->get("pos1-set"));
				return true;
			break;
			case "pos2":
				foreach($this->areas as $area){
					if($area->isInside($sender)){
						$sender->sendMessage($this->getConfig()->get("position-conflict"));
						return true;
					}
				}
				$this->selections[$sender->getName()]["pos2"] = [$sender->getFloorX(), $sender->getFloorY(), $sender->getFloorZ(), $sender->getLevel()->getName()];
				$sender->sendMessage($this->getConfig()->get("pos2-set"));
				return true;
			break;
			case "create":
				if(!isset($this->selections[$sender->getName()]["pos1"])){
					$sender->sendMessage($this->getConfig()->get("sel-pos1"));
					return true;
				}
				if(!isset($this->selections[$sender->getName()]["pos2"])){
					$sender->sendMessage($this->getConfig()->get("sel-pos2"));
					return true;
				}
				if($this->selections[$sender->getName()]["pos1"][3] !== $this->selections[$sender->getName()]["pos2"][3]){
					$sender->sendMessage($this->getConfig()->get("different-levels"));
					return true;
				}
				if($this->tooManyAreas($sender) and !$sender->hasPermission("customareas.bypass")){
					$sender->sendMessage($this->getConfig()->get("max-areas"));
					return true;
				}
				if($this->isAreaTooBig($this->selections[$sender->getName()]["pos1"], $this->selections[$sender->getName()]["pos2"]) and !$sender->hasPermission("customareas.bypass")){
					$sender->sendMessage($this->getConfig()->get("big-area"));
					return true;
				}
				$this->areas[] = new Area($this, $this->selections[$sender->getName()]["pos1"], $this->selections[$sender->getName()]["pos2"], $this->selections[$sender->getName()]["pos1"][3], $sender->getName());
				$sender->sendMessage($this->getConfig()->get("area-created"));
				unset($this->selections[$sender->getName()]);
				return true;
			break;
			case "delete":
				$name = strtolower($sender->getName());
				foreach($this->areas as $key => $area){
					if($area->isInside($sender)){
						if($area->owner !== $name and !$sender->hasPermission("customareas.bypass")){
							$sender->sendMessage($this->getConfig()->get("not-owner"));
							return true;
						}
						unset($this->areas[$key]);
						$sender->sendMessage($this->getConfig()->get("area-deleted"));
						return true;
					}
				}
				$sender->sendMessage($this->getConfig()->get("stand-inside"));
				return true;
			break;
			case "whitelist":
				if(!isset($args[0])){
					$sender->sendMessage("Usage: /ca whitelist add/remove/list");
				}
				switch(strtolower(array_shift($args))){
					case "add":
						if(!isset($args[0])){
							$sender->sendMessage($this->getConfig()->get("insert-player"));
							return true;
						}
						$name = strtolower($sender->getName());
						foreach($this->areas as $key => $area){
							if($area->isInside($sender)){
								if($area->owner !== $name and !$sender->hasPermission("customareas.bypass")){
									$sender->sendMessage($this->getConfig()->get("not-owner"));
									return true;
								}
								if(!in_array(strtolower($args[0]), $area->whiteList)){
									$this->areas[$key]->whiteList[] = strtolower($args[0]);
								}
								$sender->sendMessage($this->getConfig()->get("wl-add").$args[0]);
								return true;
							}
						}
						$sender->sendMessage($this->getConfig()->get("stand-inside-wl"));
						return true;
					break;
					case "remove":
						if(!isset($args[0])){
							$sender->sendMessage($this->getConfig()->get("insert-player"));
							return true;
						}
						$name = strtolower($sender->getName());
						foreach($this->areas as $key => $area){
							if($area->isInside($sender)){
								if($area->owner !== $name and !$sender->hasPermission("customareas.bypass")){
									$sender->sendMessage($this->getConfig()->get("not-owner"));
									return true;
								}
								if(($wlKey = array_search(strtolower($args[0]), $area->whiteList)) !== false){
									unset($this->areas[$key]->whiteList[$wlKey]);
									$sender->sendMessage($this->getConfig()->get("wl-remove").$args[0]);
								}else{
									$sender->sendMessage($args[0].$this->getConfig()->get("not-in-wl"));
								}
								return true;
							}
						}
						$sender->sendMessage($this->getConfig()->get("stand-inside-wl"));
						return true;
					break;
					case "list":
						$name = strtolower($sender->getName());
						foreach($this->areas as $key => $area){
							if($area->isInside($sender)){
								if($area->owner !== $name and !$sender->hasPermission("customareas.bypass")){
									$sender->sendMessage($this->getConfig()->get("not-owner"));
									return true;
								}
								$sender->sendMessage("Whitelist: ".implode(", ", $area->whiteList));
								return true;
							}
						}
						$sender->sendMessage($this->getConfig()->get("stand-inside-wl"));
						return true;
					break;
					default:
						$sender->sendMessage("Usage: /ca whitelist add/remove/list");
						return true;
				}
			break;
		}
		return true;
	}

	private function isAreaTooBig(array $pos1, array $pos2) : bool{
		return $this->getConfig()->get("max-distance") === 0 ? false : (($pos1[0] - $pos2[0]) ** 2 + ($pos1[1] - $pos2[1]) ** 2 + ($pos1[2] - $pos2[2]) ** 2) > $this->getConfig()->get("max-distance") ** 2;
	}

	private function tooManyAreas(Player $sender) : bool{
		$count = 1;
		$name = strtolower($sender->getName());
		foreach($this->areas as $area){
			if($area->owner === $name){
				$count += 1;
			}
		}
		return $count > $this->getConfig()->get("area-limit");
	}

}