<?php

declare(strict_types=1);

namespace KumaDev\NightVision;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\player\Player;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\effect\EffectInstance;

class Main extends PluginBase implements Listener {

    private string $mode;
    private array $worlds;
    private int $checkInterval;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->mode = $this->getConfig()->get("Mode", "whitelist");
        $this->worlds = $this->getConfig()->get("Worlds", []);
        $this->checkInterval = $this->getConfig()->get("CheckInterval", 120);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            foreach ($this->getServer()->getOnlinePlayers() as $player) {
                $this->updateNightVisionEffect($player);
            }
        }), 20 * $this->checkInterval); // Repeat based on config interval
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $this->updateNightVisionEffect($player);
    }

    public function onRespawn(PlayerRespawnEvent $event): void {
        $player = $event->getPlayer();
        $this->updateNightVisionEffect($player);
    }

    public function onMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $this->updateNightVisionEffect($player);
    }

    private function shouldGiveNightVision(Player $player): bool {
        $worldName = $player->getWorld()->getFolderName();
        if ($this->mode === "whitelist") {
            return in_array($worldName, $this->worlds, true);
        } elseif ($this->mode === "blacklist") {
            return !in_array($worldName, $this->worlds, true);
        }
        return false;
    }

    private function updateNightVisionEffect(Player $player): void {
        if ($this->shouldGiveNightVision($player)) {
            $this->giveNightVision($player);
        } else {
            $this->clearNightVision($player);
        }
    }

    private function giveNightVision(Player $player): void {
        $nightVisionEffect = VanillaEffects::NIGHT_VISION();
        $effectInstance = new EffectInstance($nightVisionEffect, 2147483647, 0, false, false); // Particles set to false
        $player->getEffects()->add($effectInstance);
    }

    private function clearNightVision(Player $player): void {
        $player->getEffects()->remove(VanillaEffects::NIGHT_VISION());
    }
}

