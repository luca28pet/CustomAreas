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

    public function onEnable(){
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

    public function onDisable(){
        $data = [];
        foreach($this->areas as $area){
            $data[] = ["pos1" => $area->min, "pos2" => $area->max, "level" => $area->level, "owner" => $area->owner, "whiteList" => $area->whiteList];
        }
        file_put_contents($this->getDataFolder()."areas.json", json_encode($data));
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if(strtolower($command->getName()) === "customareas"){
            if(!($sender instanceof Player)){
                $sender->sendMessage("Please run this command in game");
                return true;
            }
            if(!isset($args[0])){
                $sender->sendMessage("CustomAreas plugin by luca28pet.");
                $sender->sendMessage($command->getUsage());
                return true;
            }
            $sub = array_shift($args);
            switch(strtolower($sub)){
                case "pos1":
                    foreach($this->areas as $area){
                        if($area->isInside($sender->getLevel()->getName(), $sender)){
                            $sender->sendMessage("This position is inside another area");
                            return true;
                        }
                    }
                    $this->selections[$sender->getName()]["pos1"] = ["x" => $sender->getFloorX(), "y" => $sender->getFloorY(), "z" => $sender->getFloorZ(), "level" => $sender->getLevel()->getName()];
                    $sender->sendMessage("Position 1 set.");
                    return true;
                break;
                case "pos2":
                    foreach($this->areas as $area){
                        if($area->isInside($sender->getLevel()->getName(), $sender)){
                            $sender->sendMessage("This position is inside another area");
                            return true;
                        }
                    }
                    $this->selections[$sender->getName()]["pos2"] = ["x" => $sender->getFloorX(), "y" => $sender->getFloorY(), "z" => $sender->getFloorZ(), "level" => $sender->getLevel()->getName()];
                    $sender->sendMessage("Position 2 set.");
                    return true;
                break;
                case "create":
                    if(!isset($this->selections[$sender->getName()]["pos1"])){
                        $sender->sendMessage("Please select position 1");
                        return true;
                    }
                    if(!isset($this->selections[$sender->getName()]["pos2"])){
                        $sender->sendMessage("Please select position 2");
                        return true;
                    }
                    if($this->selections[$sender->getName()]["pos1"]["level"] !== $this->selections[$sender->getName()]["pos2"]["level"]){
                        $sender->sendMessage("Positions are in different levels");
                        return true;
                    }
                    $pos1 = [$this->selections[$sender->getName()]["pos1"]["x"], $this->selections[$sender->getName()]["pos1"]["y"], $this->selections[$sender->getName()]["pos1"]["z"]];
                    $pos2 = [$this->selections[$sender->getName()]["pos2"]["x"], $this->selections[$sender->getName()]["pos2"]["y"], $this->selections[$sender->getName()]["pos2"]["z"]];
                    if($this->isAreaTooBig($pos1, $pos2)){
                        $sender->sendMessage("Your area is too big");
                        return true;
                    }
                    $this->areas[] = new Area($this, $pos1, $pos2, $this->selections[$sender->getName()]["pos2"]["level"], $sender->getName());
                    $sender->sendMessage("Area created succesfully");
                    unset($this->selections[$sender->getName()]);
                    return true;
                break;
                case "delete":
                    foreach($this->areas as $key => $area){
                        if($area->isInside($sender->getLevel()->getName(), $sender)){
                            if($area->owner !== strtolower($sender->getName()) and !$sender->hasPermission("customareas.bypass")){
                                $sender->sendMessage("This is not your area");
                            }
                            unset($this->areas[$key]);
                            $sender->sendMessage("Area deleted");
                            return true;
                        }
                    }
                    $sender->sendMessage("Stand inside your area and type this command to delete it");
                    return true;
                break;
                case "whitelist":
                    if(!isset($args[0])){
                        $sender->sendMessage("Usage: /area whitelist add/remove/list");
                    }
                    $action = array_shift($args);
                    switch(strtolower($action)){
                        case "add":
                            if(!isset($args[0])){
                                $sender->sendMessage("Please specify a player");
                                return true;
                            }
                            foreach($this->areas as $key => $area){
                                if($area->isInside($sender->getLevel()->getName(), $sender)){
                                    if($area->owner !== strtolower($sender->getName()) and !$sender->hasPermission("customareas.bypass")){
                                        $sender->sendMessage("This is not your area");
                                        return true;
                                    }
                                    if(!in_array(strtolower($args[0]), $area->whiteList)){
                                        $this->areas[$key]->whiteList[] = strtolower($args[0]);
                                    }
                                    $sender->sendMessage("Added ".$args[0]." to whiteList");
                                    return true;
                                }
                            }
                            $sender->sendMessage("Stand inside your area and type this command to modify the whiteList");
                            return true;
                        break;
                        case "remove":
                            if(!isset($args[0])){
                                $sender->sendMessage("Please specify a player");
                                return true;
                            }
                            foreach($this->areas as $key => $area){
                                if($area->isInside($sender->getLevel()->getName(), $sender)){
                                    if($area->owner !== strtolower($sender->getName()) and !$sender->hasPermission("customareas.bypass")){
                                        $sender->sendMessage("This is not your area");
                                        return true;
                                    }
                                    if(($wlKey = array_search(strtolower($args[0]), $area->whiteList)) !== false){
                                        unset($this->areas[$key]->whiteList[$wlKey]);
                                        $sender->sendMessage("Removed ".$args[0]." from whiteList");
                                    }else{
                                        $sender->sendMessage($args[0]." was not in this area whiteList");
                                    }
                                    return true;
                                }
                            }
                            $sender->sendMessage("Stand inside your area and type this command to modify the whiteList");
                            return true;
                        break;
                        case "list":
                            foreach($this->areas as $key => $area){
                                if($area->isInside($sender->getLevel()->getName(), $sender)){
                                    if($area->owner !== strtolower($sender->getName()) and !$sender->hasPermission("customareas.bypass")){
                                        $sender->sendMessage("This is not your area");
                                        return true;
                                    }
                                    $sender->sendMessage("Whitelist: ".implode(", ", $area->whiteList));
                                    return true;
                                }
                            }
                            $sender->sendMessage("Stand inside your area and type this command to see the whiteList");
                            return true;
                        break;
                        default:
                            $sender->sendMessage("Usage: /area whitelist add/remove/list");
                            return true;
                    }
                break;
            }
        }
        return true;
    }

    private function isAreaTooBig(array $pos1, array $pos2){
        return $this->getConfig()->get("max-distance") === 0 ? false : (($pos1["x"] - $pos2["x"]) ** 2 + ($pos1["y"] - $pos2["y"]) ** 2 + ($pos1["z"] - $pos2["z"]) ** 2) > $this->getConfig()->get("max-distance") ** 2;
    }

}