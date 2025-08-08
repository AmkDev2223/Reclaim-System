<?php

namespace Amk\ReclaimSystem\utils;

use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\WorldException;
use pocketmine\world\WorldManager;
use InvalidArgumentException;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\item\Item;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use function count;

class ItemSerializer {
  
  private const TAG_NAME = "contents";
  
    public static function serialize(array $contents) : string{
        if(count($contents) === 0){
            return "";
        }
        $contents_tag = [];
        foreach($contents as $slot => $item){
            $contents_tag[] = $item->nbtSerialize($slot);
        }
        return (new BigEndianNbtSerializer())->write(new TreeRoot(CompoundTag::create()->setTag(self::TAG_NAME, new ListTag($contents_tag, NBT::TAG_Compound))));
    }

    public static function deSerialize(string $string) : array{
        if($string === ""){
            return [];
        }
        $tag = (new BigEndianNbtSerializer())->read($string)->mustGetCompoundTag()->getListTag(self::TAG_NAME) ?? throw new InvalidArgumentException("Invalid serialized string specified");
        $contents = [];
        foreach($tag as $value){
            try{
                $item = Item::nbtDeserialize($value);
            }catch(SavedDataLoadingException){
                continue;
            }
            $contents[$value->getByte("Slot")] = $item;
        }
        return $contents;
    }
}