<?php

declare(strict_types=1);

namespace KumaDev\PMNightVision;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\player\Player;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

    private bool $nightVisionEnabled;
    private int $checkInterval;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->nightVisionEnabled = $this->getConfig()->get("NightVision", true);
        $this->checkInterval = $this->getConfig()->get("CheckInterval", 60);

        if ($this->nightVisionEnabled) {
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
            $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
                foreach($this->getServer()->getOnlinePlayers() as $player) {
                    $this->giveNightVision($player);
                }
            }), 20 * $this->checkInterval); // Repeat based on config interval
        }
    }

    public function onJoin(PlayerJoinEvent $event): void {
        if ($this->nightVisionEnabled) {
            $player = $event->getPlayer();
            $this->giveNightVision($player);
        }
    }

    public function onRespawn(PlayerRespawnEvent $event): void {
        if ($this->nightVisionEnabled) {
            $player = $event->getPlayer();
            $this->giveNightVision($player);
        }
    }

    private function giveNightVision(Player $player): void {
        $nightVisionEffect = VanillaEffects::NIGHT_VISION();
        $effectInstance = new \pocketmine\entity\effect\EffectInstance($nightVisionEffect, 2147483647, 0, false, false); // Particles set to false
        $player->getEffects()->add($effectInstance);
    }
}
