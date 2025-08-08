<?php

namespace Amk\ReclaimSystem;

use pocketmine\plugin\PluginBase;

use muqsit\invmenu\InvMenuHandler;

use Amk\ReclaimSystem\command\ReclaimCommand;
use Amk\ReclaimSystem\data\DataManager;
use Amk\ReclaimSystem\handlers\ReclaimManager;

final class Main extends PluginBase {

    private static ?Main $instance = null;

    private DataManager $dataManager;
    private ReclaimManager $reclaimManager;

    public static function getInstance(): Main {
        return self::$instance;
    }

    public function onEnable(): void {
        self::$instance = $this;

        @mkdir($this->getDataFolder(), 0777, true);

        $this->dataManager = new DataManager($this->getDataFolder());
        $this->reclaimManager = new ReclaimManager();

        @mkdir($this->getDataFolder() . "reclaims/", 0777, true);
        
        $this->getServer()->getCommandMap()->register("reclaim", new ReclaimCommand());
        
        if (!InvMenuHandler::isRegistered()) {
          InvMenuHandler::register($this);
        }

        $this->getLogger()->info("Reclaim Enabled.");
    }

    public function onDisable(): void {
        $this->getLogger()->info("Reclaim disabled.");
        self::$instance = null;
    }

    public function getDataManager(): DataManager {
        return $this->dataManager;
    }

    public function getReclaimManager(): ReclaimManager {
        return $this->reclaimManager;
    }
}