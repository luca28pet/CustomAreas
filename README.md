**PocketMine Plugin for protecting areas**
CustomAreas is a PocketMine-MP plugin that provide players a way to protect their buildings using simple commands.

Latest release: https://poggit.pmmp.io/p/CustomAreas/

Latest development phars: https://poggit.pmmp.io/ci/luca28pet/CustomAreas/CustomAreas

**Current features:**

- Create private areas where other players cannot build
- Provide protection for blocks, chests, doors and trapdoors inside the area
- Whitelist for areas: an area owner can choose who can build in his zone
- Support for multi-world
- Bypass feature for OPs: they can edit other players areas (or use permission node customareas.bypass)
- Easy to use
- Configurable
- Limit area size and number


**Commands:**
Main Command: /customareas
Aliases: /ca

- _/ca pos1_ : set the first corner of the area
- _/ca pos2_ : set the second corner of the area
- _/ca create_ : create an area with the current selection
- _/ca delete_ : delete the area where you are standing in (OPs can delete other players areas)
- _/ca whitelist add_ : add a player to the whitelist of the area where you are standing in (OPs can edit other players areas whitelist)
- _/ca whitelist remove_ : remove a player to the whitelist of the area where you are standing in (OPs can edit other players areas whitelist)
- _/ca whitelist list_ : see the list of the whitelisted players of the area where you are standing in (OPs can see other players areas whitelist)


**Permission nodes:**

- _customareas.command_ : use command /area (defaut: true)
- _customareas.bypass_ : use bypass feature to edit other players' areas (default: op)

**Config**

```
---
# Max distance between the two corners of the area. Set 0 (zero) to disable
max-distance: 50
# Max number of areas that a player can have
area-limit: 2

#Language
position-conflict: "This position is inside another area"
pos1-set: "Position 1 set"
pos2-set: "Position 2 set"
sel-pos1: "Please select position 1"
sel-pos2: "Please select position 2"
different-levels: "Positions are in different levels"
max-areas: "You have too many areas"
big-area: "Your area is too big"
area-created: "Area created succesfully"
not-owner: "This is not your area"
area-deleted: "Area deleted"
stand-inside: "Stand inside your area and type this command to delete it"
insert-player: "Please specify a player"
wl-add: "Added to whitelist the player: "
stand-inside-wl: "Stand inside your area and type this command to modify the whiteList"
wl-remove: "Removed from whiteList the player: "
not-in-wl: " was not in this area whiteList"
notice: "This is {owner}'s private area"
...
```