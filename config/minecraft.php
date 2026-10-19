<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Minecraft tools
    |--------------------------------------------------------------------------
    |
    | Registry of the tools in the Minecraft section (sidebar group and the
    | /minecraft overview). Only list tools that are built: an entry needs a
    | named route that exists. A new tool only adds an entry here.
    |
    | id          unique, kebab-case
    | title       translation key (lang/*.json)
    | description translation key, one line for the overview page
    | icon        Font Awesome classes
    | route       route name of the tool page
    | permission  permission slug that grants access to the tool
    |
    */

    'tools' => [
        [
            'id' => 'anvil-planner',
            'title' => 'anvil enchantment planner',
            'description' => 'Cheapest order to apply enchanted books in an anvil, with level and XP cost.',
            'icon' => 'fa-solid fa-hammer',
            'route' => 'minecraft.anvil-planner',
            'permission' => 'view.minecraft.anvil.planner',
        ],
        [
            'id' => 'farm-catalogue',
            'title' => 'farm module catalogue',
            'description' => 'Community farm designs per version with rates, footprint, materials and sources.',
            'icon' => 'fa-solid fa-wheat-awn',
            'route' => 'minecraft.farm-catalogue',
            'permission' => 'view.minecraft.farm.catalogue',
        ],
        [
            'id' => 'chain-planner',
            'title' => 'farm chain planner',
            'description' => 'Production chain of an item down to raw resources, with farm modules, furnaces and hopper lines.',
            'icon' => 'fa-solid fa-diagram-project',
            'route' => 'minecraft.chain-planner',
            'permission' => 'view.minecraft.chain.planner',
        ],
        [
            'id' => 'farm-viewer',
            'title' => '3D farm viewer',
            'description' => 'Fly through a farm design in 3D and step through it layer by layer, with a material list.',
            'icon' => 'fa-solid fa-cubes',
            'route' => 'minecraft.farm-viewer',
            'permission' => 'view.minecraft.farm.viewer',
        ],
        [
            'id' => 'portal-calculator',
            'title' => 'nether portal calculator',
            'description' => 'Overworld and Nether coordinates, portal links, frame materials and 3D distances.',
            'icon' => 'fa-solid fa-dungeon',
            'route' => 'minecraft.portal-calculator',
            'permission' => 'view.minecraft.portal.calculator',
        ],
        [
            'id' => 'stack-calculator',
            'title' => 'stack calculator',
            'description' => 'Convert between items, stacks, chests and shulker boxes, in any mix.',
            'icon' => 'fa-solid fa-boxes-stacked',
            'route' => 'minecraft.stack-calculator',
            'permission' => 'view.minecraft.stack.calculator',
        ],
        [
            'id' => 'material-calculator',
            'title' => 'material calculator',
            'description' => 'Blocks for boxes, rooms, walls and frames, in stacks and shulker boxes.',
            'icon' => 'fa-solid fa-cube',
            'route' => 'minecraft.material-calculator',
            'permission' => 'view.minecraft.material.calculator',
        ],
        [
            'id' => 'xp-calculator',
            'title' => 'XP calculator',
            'description' => 'Levels and experience points, and how many mobs, bottles or smelted items a level takes.',
            'icon' => 'fa-solid fa-star',
            'route' => 'minecraft.xp-calculator',
            'permission' => 'view.minecraft.xp.calculator',
        ],
        [
            'id' => 'smelt-calculator',
            'title' => 'smelting calculator',
            'description' => 'Time, fuel and XP for a furnace array, with every fuel compared.',
            'icon' => 'fa-solid fa-fire',
            'route' => 'minecraft.smelt-calculator',
            'permission' => 'view.minecraft.smelt.calculator',
        ],
        [
            'id' => 'craft-calculator',
            'title' => 'crafting calculator',
            'description' => 'Crafts and raw materials for any amount of an item, with every way compared.',
            'icon' => 'fa-solid fa-hammer',
            'route' => 'minecraft.craft-calculator',
            'permission' => 'view.minecraft.craft.calculator',
        ],
        [
            'id' => 'circle-generator',
            'title' => 'circle and sphere generator',
            'description' => 'Block patterns for circles, ellipses, spheres and domes, layer by layer.',
            'icon' => 'fa-regular fa-circle',
            'route' => 'minecraft.circle-generator',
            'permission' => 'view.minecraft.circle.generator',
        ],
        [
            'id' => 'slime-finder',
            'title' => 'slime chunk finder',
            'description' => 'Slime chunks for a world seed and the best AFK spot for a slime farm.',
            'icon' => 'fa-solid fa-droplet',
            'route' => 'minecraft.slime-finder',
            'permission' => 'view.minecraft.slime.finder',
        ],
    ],

];
