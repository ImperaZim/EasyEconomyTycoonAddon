<?php

namespace ImperaZim\EasyEconomy;

use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use pocketmine\event\player\PlayerChatEvent;

use ImperaZim\EasyEconomy\EasyEconomy;
use ImperaZim\EasyEconomy\event\TycoonUpdateEvent;
use ImperaZim\EasyEconomy\event\PlayerMoneyUpdateEvent;

final class EasyEconomyTycoonAddon extends PluginBase implements Listener {

  public $cfg;
  public $tycoon;
  public $provider;

  protected static ?EasyEconomyTycoonAddon $instance = null;

  public static function getInstance() : ?EasyEconomyTycoonAddon {
    return self::$instance;
  }

  public function onLoad() : void {
    self::$instance = $this;
  }

  public function onEnable() : void {
    self::$instance = $this;
    $this->getScheduler()->scheduleRepeatingTask(new Task($this),20);
  }

  public function onMoneyUpdate(PlayerMoneyUpdateEvent $event) : void {
    $easyeconomy = $this->getServer()->getPluginManager()->getPlugin("EasyEconomy");
    $top = null;
    foreach ($easyeconomy->getProvider()->getAllInOrder() as $hash => $data) {
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

    $easyeconomy = $this->getServer()->getPluginManager()->getPlugin("EasyEconomy");
    if ($easyeconomy->getConfig()->getNested('addon.tycoon.enable', false)) {
      $tag = $easyeconomy->getConfig()->setNested('addon.tycoon.chat_tag');
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