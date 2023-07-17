<?php

namespace ImperaZim\EasyEconomy;

use pocketmine\Server;
use pocketmine\scheduler\CancelTaskException;

class Task extends \pocketmine\scheduler\Task {
  private ?EasyEconomy $plugin;
  private ?EasyEconomyTycoonAddon $addon;

  public int $secounds = 5;

  public function __construct(EasyEconomyTycoonAddon $addon) {
    $this->addon = $addon;
    $this->plugin = Server::getInstance()->getPluginManager()->getPlugin("EasyEconomy");
  }

  public function onRun() : void {
    if ($this->secounds <= 0) {
      if (($plugin = $this->plugin) === null) {
        throw new \InvalidArgumentException("It is not possible to start the addon \"EasyEconomyTycoonAddon\" because the \"EasyEconomy\" plugin is not installed on the server!");
      }
      $cfg = $plugin->getConfig();
      $provider = $plugin->getProvider();

      $dr = 'addon.tycoon';
      $data = $cfg->getNested($dr, null);
      if ($data === null) {
        if (!isset($data['enable'])) {
          $cfg->setNested($dr . '.enable', true);
        }
        if (!isset($data['chat_tag'])) {
          $cfg->setNested($dr . '.chat_tag', '[$]');
        }
        $cfg->save();
      }

      foreach ($provider->getAllInOrder() as $hash => $data) {
        $this->addon->tycoon = $data['name'];
        break;
      }
      
      $this->addon->getServer()->getPluginManager()->registerEvents($this->addon, $this->addon);
      $this->addon->getLogger()->notice('Addon tycoon started!');
      throw new CancelTaskException();
    }
    $this->secounds = $this->secounds - 1;
  }
}