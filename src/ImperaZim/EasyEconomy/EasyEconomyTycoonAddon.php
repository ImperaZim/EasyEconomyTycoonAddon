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

  public static function getInstance() : ?EasyEconomyTycoonAddon {
    return self::$instance;
  }

  public function onLoad() : void {
    self::$instance = $this;
  }

  public function onEnable() : void {
    self::$instance = $this;
    if ($this->getServer()->getPluginManager()->getPlugin("EasyEconomy") === null) {
      throw new \InvalidArgumentException("It is not possible to start the addon \"EasyEconomyTycoonAddon\" because the \"EasyEconomy\" plugin is not installed on the server!");
      return;
    }

    $this->config = EasyEconomy::getInstance()->getConfig();
    $this->provider = EasyEconomy::getInstance()->getProvider();

    if ($this->config->getNested('addon.tycoon', null) == null) {
      $data = $this->config->getNested('addon.tycoon', null);
      if ($data == null || isset($data['enable'])) {
        $this->config->setNested('addon.tycoon.enable', true);
      }
      if ($data == null || isset($data['chat_tag'])) {
        $this->config->setNested('addon.tycoon.chat_tag', '[$]');
      }
      $this->config->save();
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

    if ($this->config->getNested('addon.tycoon.enable', false)) {
      $tag = $this->config->setNested('addon.tycoon.chat_tag');
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