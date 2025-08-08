<?php

namespace Amk\ReclaimSystem\command;

use Amk\ReclaimSystem\Main;
use Amk\ReclaimSystem\utils\ItemSerializer;
use Amk\ReclaimSystem\utils\TimeParse;
use Amk\ReclaimSystem\menu\ReclaimEditMenu;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class ReclaimCommand extends Command {

    public function __construct() {
        parent::__construct("reclaim");
        $this->setPermission("reclaim.command.permission");
    }

    public function execute(CommandSender $sender, string $label, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game.");
            return;
        }

        if (!$sender->hasPermission("reclaim.command.permission")) {
            return;
        }

        $rm = Main::getInstance()->getReclaimManager();

        if (count($args) === 0) {
    $result = $rm->giveReclaim($sender);

    if ($result === false) {
    $sender->sendMessage("There is no items for the reclaim")
    } elseif (is_string($result)) {
      $cooldownLeft = $rm->getCooldownRemaining($sender, $result);
      if ($cooldownLeft > 0) {
        $cooldownFormatted = TimeParse::parse($cooldownLeft);
            $sender->sendMessage("You have already claimed your items of " . $result . ", you have cooldown of " . $cooldownFormatted . ".");
      } else {
        $sender->sendMessage("You have successfully reclaimed your items of " . $reclaim . ".");
      }
    }
    return;
        }

        $sub = strtolower(array_shift($args));

        if ($sub === "create") {
          if (!$sender->hasPermission("reclaim.admin.command.permission")) {
            $sender->sendMessage("You do not have permission to use this command.");
            return;
          }
            $name = $args[0] ?? null;
            if ($name === null) {
                $sender->sendMessage("Usage: /reclaim create <name>");
                return;
            }
            if ($rm->createReclaim($name)) {
                $sender->sendMessage("Reclaim " . $name . " created with default cooldown: 3h.");
            } else {
                $sender->sendMessage("Reclaim " . $name . " already exists.");
            }
            return;
        }

        if ($sub === "delete") {
          if (!$sender->hasPermission("reclaim.admin.command.permission")) {
            $sender->sendMessage("You do not have permission to use this command.");
            return;
          }
            $name = $args[0] ?? null;
            if ($name === null) {
                $sender->sendMessage("Usage: /reclaim delete <name>");
                return;
            }
            if ($rm->deleteReclaim($name)) {
                $sender->sendMessage("Reclaim " . $name . " has been deleted.");
            } else {
                $sender->sendMessage("Reclaim " . $name . " does not exist.");
            }
            return;
        }
        
        if ($sub === "resetcooldown") {
    if (!$sender->hasPermission("reclaim.admin.command.permission")) {
        $sender->sendMessage("You do not have permission to use this command.");
        return;
    }

    $targetName = $args[0] ?? null;
    $reclaimName = $args[1] ?? null;

    if ($targetName === null || $reclaimName === null) {
        $sender->sendMessage("Usage: /reclaim resetcooldown <player> <reclaim>");
        return;
    }

    $target = $sender->getServer()->getPlayerByPrefix($targetName);
    if ($target === null) {
        $sender->sendMessage("Player . " . $targetName . " is not online.");
        return;
    }

    $rm = Main::getInstance()->getReclaimManager();

    if ($rm->getReclaim($reclaimName) === null) {
        $sender->sendMessage("Reclaim " . $reclaimName . " does not exist.");
        return;
    }

    $playerName = strtolower($target->getName());
    if (isset($rm->cooldowns[$playerName][$reclaimName])) {
        unset($rm->cooldowns[$playerName][$reclaimName]);
        $rm->saveCooldowns();
        $sender->sendMessage("There is no more cooldown for " . $reclaimName . "for the player " . $targetName . ".");
    } else {
        $sender->sendMessage("No cooldown found for player " . $targetName);
    }
    return;
        }
        if ($sub === "list") {
          if (!$sender->hasPermission("reclaim.admin.command.permission")) {
        $sender->sendMessage("You do not have permission to use this command.");
        return;
          }
          $rm = Main::getInstance()->getReclaimManager();
          $reclaimNames = array_keys($rm->getReclaims());
          
          if (empty($reclaimNames)) {
            $sender->sendMessage("No reclaims found.");
            return;
          }
          $sender->sendMessage("Reclaims available: " . implode(", ", $reclaimNames));
          return;
        }

        if ($sub === "edit") {
          if (!$sender->hasPermission("reclaim.admin.command.permission")) {
            $sender->sendMessage("You do not have permission to use this command.");
            return;
          }
            $name = $args[0] ?? null;
            $field = $args[1] ?? null;
            $value = $args[2] ?? null;

            if ($name === null || $field === null) {
                $sender->sendMessage("Usage: /reclaim edit <name> <cooldown|permission|rewards> [value]");
                return;
            }

            $reclaim = $rm->getReclaim($name);
            if ($reclaim === null) {
                $sender->sendMessage("Reclaim " . $name . "does not exist.");
                return;
            }

            if ($field === "cooldown") {
                if ($value === null) {
                    $sender->sendMessage("Usage: /reclaim edit <name> cooldown <time>");
                    return;
                }
                $seconds = TimeParse::parse($value);
                if ($seconds <= 0) {
                    $sender->sendMessage("Invalid time format. Use 1s, 1m, 1h, 1d.");
                    return;
                }
                $rm->setCooldown($name, $seconds);
                $sender->sendMessage("Cooldown for " . $name . " is now set to " . $value . ".");
                return;
            }

            if ($field === "permission") {
                if ($value === null || strtolower($value) === "none") {
                    $rm->setPermission($name, null);
                    $sender->sendMessage("Permission for " . $name . "removed, reclaim is now free.");
                } else {
                    $rm->setPermission($name, $value);
                    $sender->sendMessage("Permission for " . $name . " set to " . $value ".");
                }
                return;
            }

            if ($field === "rewards") {
              ReclaimEditMenu::open($sender, $name);
                return;
            }

            $sender->sendMessage("Unknown field " . $field . " Valid: cooldown, permission, rewards.");
            return;
        }

        $sender->sendMessage("Unknown subcommand. Usage:");
        $sender->sendMessage("/reclaim");
        $sender->sendMessage("/reclaim create <name>");
        $sender->sendMessage("/reclaim delete <name>");
        $sender->sendMessage("/reclaim list");
        $sender->sendMessage("/reclaim resetcooldown <player>");
        $sender->sendMessage("/reclaim edit <name> cooldown <time>");
        $sender->sendMessage("/reclaim edit <name> permission <permission|none>");
        $sender->sendMessage("/reclaim edit <name> rewards");
    }
}