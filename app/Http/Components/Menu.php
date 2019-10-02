<?php

namespace App\Http\Components;



trait Menu {

    private $default_icon = '<i class="icon-file"></i>';

    public function menu() {
        return [
            ['visible' => true, 'tag' => 'dashboard', 'lang_tag' => "dashboard", 'url' => route('dashboard'), 'icon' => '<i class="icon-home"></i>',],
            // ['visible' => true, 'tag' => 'leaderboard', 'lang_tag' => "leaderboard", 'url' => route('leaderboard'), 'icon' => '<i class="icon-flag"></i>',],
            ['visible' => $this->checkMenuPermission('users'), 'tag' => 'event', 'lang_tag' => "event", 'url' => route('event'), 'icon' => '<i class="icon-trophy"></i>',],
          

            // ['visible' => true, 'tag' => 'myevents', 'lang_tag' => "myevents", 'url' => route('myevents'), 'icon' => '<i class="icon-cup"></i>',],
            ['visible' => true, 'tag' => 'myteam', 'lang_tag' => "team", 'url' => route('teams'), 'icon' => '<i class="icon-user"></i>',],
            ['visible' => true, 'tag' => 'projects', 'lang_tag' => "projects", 'url' => route('projects'), 'icon' => '<i class="icon-layers"></i>',],
            ['visible' => true, 'tag' => 'appsubmission', 'lang_tag' => "appsubmission", 'url' => route('appsubmission'), 'icon' => '<i class="icon-drawer"></i>',],
            
            ['visible' => $this->hasItems(['users','groups']), 'tag' => 'heading', 'lang_tag' => 'operation_setup'],
            ['visible' => $this->checkMenuPermission('users'), 'tag' => 'users', 'lang_tag' => "users", 'url' => route('users'), 'icon' => '<i class="icon-user"></i>',],
            ['visible' => $this->checkMenuPermission('groups'), 'tag' => 'groups', 'lang_tag' => "groups", 'url' => route('groups'), 'icon' => '<i class="icon-key"></i>',],            
            
        ];
    }

    public function hasItems($options)
    {
        foreach($options as $option) {
            if($this->checkMenuPermission($option)){
                return true;
            }
        }
        return false;
    }

    public function getIcons($nav, $plural = false)
    {
        foreach ($this->menu() as $item) {
            if($item['tag'] == $nav) {
                if($plural && isset($item['icons'])) {
                    return $item['icons'];
                }else {
                    return $item['icon'];
                }
            }
        }
        return $this->default_icon;
    }
}
