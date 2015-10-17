<?php

namespace Khinenw\AruPG;

use pocketmine\item\Item;
use pocketmine\nbt\tag\String;
use pocketmine\utils\TextFormat;

class ItemHelper{
    const CLASS_NORMAL = 0;
    const CLASS_RARE = 1;
    const CLASS_SUPER_RARE = 2;
    const CLASS_ULTRA_RARE = 3;
    const CLASS_SPECIAL = 4;
    const CLASS_LEGENDARY = 5;

    public static $ITEM_CLASS = [
        self::CLASS_NORMAL => [
            "TID" => "NORMAL",
            "COLOR" => TextFormat::GRAY
        ],

        self::CLASS_RARE => [
            "TID" => "RARE",
            "COLOR" => TextFormat::BLUE
        ],

        self::CLASS_SUPER_RARE => [
            "TID" => "SUPER_RARE",
            "COLOR" => TextFormat::YELLOW
        ],

        self::CLASS_ULTRA_RARE => [
            "TID" => "ULTRA_RARE",
            "COLOR" => TextFormat::GOLD
        ],

        self::CLASS_SPECIAL => [
            "TID" => "SPECIAL",
            "COLOR" => TextFormat::AQUA
        ],

        self::CLASS_LEGENDARY => [
            "TID" => "LEGENDARY",
            "COLOR" => TextFormat::RED
        ]
    ];

    public function setRPGItem(Item $item, $name, $desc, $class){
        $classData = self::$ITEM_CLASS[$class];
        $item->setCustomName($classData["COLOR"] . ToAruPG::getTranslation($classData["TID"]) . $name);

        $tag = $item->getNamedTag();
        $tag["type"] = new String("Desc", $desc);
        $item->setNamedTag($tag);

        return $item;
    }
}
