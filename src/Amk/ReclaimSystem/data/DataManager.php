<?php

namespace Amk\ReclaimSystem\data;

use pocketmine\plugin\PluginBase;

class DataManager {

    private string $dataFolder;

    public function __construct(string $dataFolder) {
        $this->dataFolder = rtrim($dataFolder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        
        if (!is_dir($this->dataFolder)) {
            @mkdir($this->dataFolder, 0777, true);
        }
    }

    public function saveData(string $filename, array $data): bool {
        $path = $this->dataFolder . $filename;
        $yaml = yaml_emit($data);
        return file_put_contents($path, $yaml) !== false;
    }

    public function loadData(string $filename): array {
        $path = $this->dataFolder . $filename;
        if (!file_exists($path)) {
            return [];
        }
        $data = yaml_parse_file($path);
        if (!is_array($data)) {
            return [];
        }
        return $data;
    }

    public function exists(string $filename): bool {
        return file_exists($this->dataFolder . $filename);
    }

    public function delete(string $filename): bool {
        $path = $this->dataFolder . $filename;
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }
}