<?php

namespace ImperaZim\EasyEconomy;

use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerChatEvent;

use ImperaZim\EasyEconomy\EasyEconomy;
use ImperaZim\EasyEconomy\event\TycoonUpdateEvent;
use ImperaZim\EasyEconomy\event\PlayerMoneyUpdateEvent;

final class EasyEconomyTycoonAddon extends PluginBase implements Listener {

  protected static ?EasyEconomyTycoonAddon $instance = null;
  
  public $cfg;
  public $tycoon;
  public $provider;

  public static function getInstance() : ?EasyEconomyTycoonAddon {
    return self::$instance;
  }

  public function onLoad() : void {
    self::$instance = $this;
  }

  public function onEnable() : void {
    self::$instance = $this;
    if ($ee = ($this->getServer()->getPluginManager()->getPlugin("EasyEconomy")) === null) {
      throw new \InvalidArgumentException("It is not possible to start the addon \"EasyEconomyTycoonAddon\" because the \"EasyEconomy\" plugin is not installed on the server!");
    }
    
    $this->cfg = $ee->getConfig();
    $this->provider = $ee->getProvider();

    if (($data = $this->cfg->getNested('addon.tycoon', null))) {
      if (!isset($data['enable'])) {
        $this->cfg->setNested('addon.tycoon.enable', true);
      }
      if (!isset($data['chat_tag'])) {
        $this->cfg->setNested('addon.tycoon.chat_tag', '[$]');
      }
      $this->cfg->save();
    }

    foreach ($this->provider->getAllInOrder() as $hash => $data) {
      $this->tycoon = $data['name'];
      break;
    }
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function onMoneyUpdate(PlayerMoneyUpdateEvent $event) : void {
    $top = null;
    foreach ($this->provider->getAllInOrder() as $hash => $data) {
      $top = $data['name'];
      break;
    }
    if ($top != null && $this->tycoon != $top) {
      $ev = new TycoonUpdateEvent($this->tycoon, $top);
      $ev->call();
      $this->tycoon = $top;
    }
  }

  public function onTycoonUpdate(TycoonUpdateEvent $event) : void {
    $old = $this->getServer()->getPlayerByPrefix($event->getOld());
    $new = $this->getServer()->getPlayerByPrefix($event->getNew());

    if ($this->cfg->getNested('addon.tycoon.enable', false)) {
      $tag = $this->cfg->setNested('addon.tycoon.chat_tag');
      if ($tag != null) {
        if ($old instanceof Player) {
          $old->setNameTag(str_replace($tag . ' ', '', $old->getNameTag()));
        }
        if ($new instanceof Player) {
          $new->setNameTag($tag . ' ' . $new->getNameTag());
        }
      }
    }
  }

}