<?php

namespace CustomAreas;

use pocketmine\level\Position;
use pocketmine\Player;

class Area{

    public $plugin, $min, $max, $level, $owner, $whiteList;

    public function __construct(Main $plugin, array $pos1, array $pos2, $levelName, $owner, array $whiteList = []){
        $this->plugin = $plugin;
        $this->min = [min($pos1[0], $pos2[0]), min($pos1[1], $pos2[1]), min($pos1[2], $pos2[2])];
        $this->max = [max($pos1[0], $pos2[0]), max($pos1[1], $pos2[1]), max($pos1[2], $pos2[2])];
        $this->level = strtolower($levelName);
        $this->owner = strtolower($owner);
        $this->whiteList = array_map("strtolower", $whiteList);
    }

    public function isInside(Position $p){
        return (strtolower($p->getLevel()->getName()) === $this->level and $p->x >= $this->min[0] and $p->x <= $this->max[0] and $p->y >= $this->min[1] and $p->y <= $this->max[1] and $p->z >= $this->min[2] and $p->z <= $this->max[2]);
    }

    public function canBuild(Player $player){
        return (strtolower($player->getName()) === $this->owner or in_array(strtolower($player->getName()), $this->whiteList));
    }

}