<?php

namespace Amk\ReclaimSystem\handlers;

use Amk\ReclaimSystem\Main;
use Amk\ReclaimSystem\utils\ItemSerializer;
use pocketmine\player\Player;

class ReclaimManager {

    private array $reclaims = [];
    private array $cooldowns = [];
    private string $reclaimsFolder;
    private string $cooldownsFile;

    public function __construct() {
        $plugin = Main::getInstance();
        $this->reclaimsFolder = $plugin->getDataFolder() . "reclaims/";
        $this->cooldownsFile = $plugin->getDataFolder() . "cooldowns.yml";
        $this->loadReclaims();
        $this->loadCooldowns();
    }

    public function loadReclaims(): void {
        $this->reclaims = [];
        if (!is_dir($this->reclaimsFolder)) return;
        foreach (glob($this->reclaimsFolder . "*.yml") as $file) {
            $name = basename($file, ".yml");
            $data = yaml_parse_file($file);
            if (!is_array($data)) continue;
            $this->reclaims[$name] = [
                "permission" => $data["permission"] ?? null,
                "cooldown" => $data["cooldown"] ?? 10800,
                "items" => $data["items"] ?? []
            ];
        }
    }

    public function saveReclaim(string $name): bool {
        if (!isset($this->reclaims[$name])) return false;
        $path = $this->reclaimsFolder . $name . ".yml";
        return file_put_contents($path, yaml_emit($this->reclaims[$name])) !== false;
    }

    public function createReclaim(string $name): bool {
        if (isset($this->reclaims[$name])) return false;
        $this->reclaims[$name] = [
            "permission" => null,
            "cooldown" => 10800,
            "items" => []
        ];
        return $this->saveReclaim($name);
    }

    public function deleteReclaim(string $name): bool {
        if (!isset($this->reclaims[$name])) return false;
        unset($this->reclaims[$name]);
        $file = $this->reclaimsFolder . $name . ".yml";
        if (file_exists($file)) unlink($file);
        foreach ($this->cooldowns as $player => $reclaims) {
            if (isset($reclaims[$name])) {
                unset($this->cooldowns[$player][$name]);
            }
        }
        $this->saveCooldowns();
        return true;
    }

    public function setCooldown(string $name, int $seconds): bool {
        if (!isset($this->reclaims[$name])) return false;
        $this->reclaims[$name]["cooldown"] = $seconds;
        return $this->saveReclaim($name);
    }

    public function setPermission(string $name, ?string $permission): bool {
        if (!isset($this->reclaims[$name])) return false;
        $this->reclaims[$name]["permission"] = $permission;
        return $this->saveReclaim($name);
    }

    public function setItems(string $name, array $items): bool {
        if (!isset($this->reclaims[$name])) return false;
        $this->reclaims[$name]["items"] = $items;
        return $this->saveReclaim($name);
    }

    public function getReclaim(string $name): ?array {
        return $this->reclaims[$name] ?? null;
    }

    public function loadCooldowns(): void {
        if (!file_exists($this->cooldownsFile)) {
            $this->cooldowns = [];
            return;
        }
        $data = yaml_parse_file($this->cooldownsFile);
        if (!is_array($data)) {
            $this->cooldowns = [];
            return;
        }
        $this->cooldowns = $data;
    }

    public function saveCooldowns(): void {
        file_put_contents($this->cooldownsFile, yaml_emit($this->cooldowns));
    }

    public function canClaimReclaim(Player $player, string $reclaimName): bool {
        $playerName = strtolower($player->getName());
        $now = time();
        if (!isset($this->cooldowns[$playerName][$reclaimName])) return true;
        return $now >= $this->cooldowns[$playerName][$reclaimName];
    }

    public function getCooldownRemaining(Player $player, string $reclaimName): int {
        $playerName = strtolower($player->getName());
        $now = time();
        if (!isset($this->cooldowns[$playerName][$reclaimName])) return 0;
        $rem = $this->cooldowns[$playerName][$reclaimName] - $now;
        return $rem > 0 ? $rem : 0;
    }

    public function getAvailableReclaimsForPlayer(Player $player): array {
        $available = [];
        foreach ($this->reclaims as $name => $data) {
            $perm = $data["permission"];
            if ($perm === null || $player->hasPermission($perm)) {
                $available[$name] = $data;
            }
        }
        uasort($available, function($a, $b){
            return $a["cooldown"] <=> $b["cooldown"];
        });
        return $available;
    }
    
    public function reloadReclaim(string $name): void {
    $file = $this->reclaimsFolder . $name . ".yml";
    if (!file_exists($file)) {
        unset($this->reclaims[$name]);
        return;
    }
    $data = yaml_parse_file($file);
    if (!is_array($data)) {
        return;
    }
    $this->reclaims[$name] = [
        "permission" => $data["permission"] ?? null,
        "cooldown" => $data["cooldown"] ?? 10800,
        "items" => $data["items"] ?? []
        ];
    }
    
    public function giveReclaim(Player $player): string|false {
    $playerName = strtolower($player->getName());
    $now = time();
    $available = $this->getAvailableReclaimsForPlayer($player);
    foreach ($available as $name => $data) {
        $cooldownLeft = $this->getCooldownRemaining($player, $name);
        if ($cooldownLeft > 0) {
            return $name;
        }
        $items = [];
        if (isset($data["items"])) {
          if (is_string($data["items"]) && $data["items"] !== "") {
            $bySlot = ItemSerializer::deSerialize($data["items"]);
            foreach ($bySlot as $slot => $it) {
              if ($it !== null && (!method_exists($it, "isNull") || !$it->isNull())) {
                $items[] = $it;
              }
            }
          }
          elseif (is_array($data["items"])) {
            foreach ($data["items"] as $k => $serialized) {
              if ($serialized instanceof \pocketmine\item\Item) {
                $it = $serialized;
                if (!method_exists($it, "isNull") || !$it->isNull()) $items[] = $it;
                continue;
              }
              if (is_array($serialized)) {
                try {
                  $it = ItemSerializer::fromSerialized($serialized);
                  if ($it !== null && (!method_exists($it, "isNull") || !$it->isNull())) $items[] = $it;
                } catch (\Throwable $e) {
                  continue;
                }
                continue;
              }
              if (is_string($serialized) && $serialized !== "") {
                $tryDecoded = @json_decode($serialized, true);
                if (is_array($tryDecoded)) {
                  try {
                    $it = ItemSerializer::fromSerialized($tryDecoded);
                    if ($it !== null && (!method_exists($it, "isNull") || !$it->isNull())) $items[] = $it;
                  } catch (\Throwable $e) {
                    continue;
                  }
                } else {
                  try {
                    $bySlot = ItemSerializer::deSerialize($serialized);
                    foreach ($bySlot as $slot => $it) {
                      if ($it !== null && (!method_exists($it, "isNull") || !$it->isNull())) {
                        $items[] = $it;
                        }
                    }
                  } catch (\Throwable $e) {
                    continue;
                  }
                }
              }
            }
          }
        }
        if (empty($items)) {
          continue;
        }
        $inventory = $player->getInventory();
        foreach ($items as $item) {
          if (!$inventory->canAddItem($item)) {
            return false;
          }
        }
        foreach ($items as $item) {
          $inventory->addItem($item);
        }
        $this->cooldowns[$playerName][$name] = $now + ($data["cooldown"] ?? 10800);
        $this->saveCooldowns();
        return $name;
    }
    return false;
    }
    
    public function getReclaims(): array {
    return $this->reclaims;
    }
}