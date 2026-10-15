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
    ],

];
