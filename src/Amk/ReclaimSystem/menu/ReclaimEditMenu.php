<?php

namespace Amk\ReclaimSystem\menu;

use Amk\ReclaimSystem\Main;
use Amk\ReclaimSystem\utils\ItemSerializer;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InventoryTransactionResult;

use pocketmine\player\Player;
use pocketmine\inventory\Inventory;

class ReclaimEditMenu {

    private string $reclaimName;

    public function __construct(string $reclaimName) {
        $this->reclaimName = $reclaimName;
    }
    
    public static function open(Player $player, string $reclaimName): void {
    $menu = InvMenu::create(InvMenu::TYPE_CHEST);
    $menu->setName("Editing Reclaim: " . $reclaimName);

    $file = Main::getInstance()->getDataFolder() . "reclaims/" . $reclaimName . ".yml";
    $data = file_exists($file) ? yaml_parse_file($file) : [];
    $items = [];
    
    if (isset($data["items"]) && is_string($data["items"])) {
        try {
            $items = ItemSerializer::deSerialize($data["items"]);
        } catch (\Throwable $e) {
            $items = [];
        }
    }
    $inventory = $menu->getInventory();
    foreach ($items as $slot => $item) {
        $inventory->setItem($slot, $item);
    }
    $menu->setInventoryCloseListener(function(Player $player, Inventory $inventory) use ($reclaimName): void {
        $contents = $inventory->getContents();
        $serializedString = ItemSerializer::serialize($contents);

        $file = Main::getInstance()->getDataFolder() . "reclaims/" . $reclaimName . ".yml";
        $data = file_exists($file) ? yaml_parse_file($file) : [];
        $data["items"] = $serializedString;
        file_put_contents($file, yaml_emit($data));
        
        Main::getInstance()->getReclaimManager()->reloadReclaim($reclaimName);
        $player->sendMessage("Reclaim " . $reclaimName . " saved.");
    });
    $menu->send($player);
    }
}