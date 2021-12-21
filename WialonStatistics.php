<?php 

// WialonStatistics использует класс WialonApiClient: https://github.com/reqweb/WialonApiClient

class WialonStatistics{
    
    public $FileFolderPath = __DIR__;
    public $dateFormat = "d.m.Y H:i";
    
    public function getRawObjects()
    {
        $WialonApiClient = new WialonApiClient();
        $objectsRaw = $WialonApiClient->searchItems("", ["flags" => 2102529]); // 2101505
        if(file_put_contents($this->FileFolderPath.'/objectsRaw.json', json_encode($objectsRaw, JSON_UNESCAPED_UNICODE)) === false){
            throw new \Exception("Не удалось записать файл objectsRaw.json");
        }
        return true;
    }
    
    public function statisticsDevice(bool $updateObjects = false, bool $parseDevice = false)
    {
        if(!file_exists($this->FileFolderPath.'/objectsRaw.json') || $updateObjects){
            $this->getRawObjects();
        }
        $objectsArray = json_decode(file_get_contents($this->FileFolderPath.'/objectsRaw.json'), true);
        $statisticsDevice = [];
        $statisticsDevice["generalInfo"]["countAll"] = count($objectsArray["items"]);
        $statisticsDevice["devicesCount"] = [];
        foreach($objectsArray["items"] as $object){
            if(array_key_exists($object["hw"], $statisticsDevice["devicesCount"])){
                $statisticsDevice["devicesCount"][$object["hw"]] = $statisticsDevice["devicesCount"][$object["hw"]] + 1;
            }else{
                $statisticsDevice["devicesCount"][$object["hw"]] = 1;
            }
        }
        $statisticsDevice["generalInfo"]["countDeviceType"] = count($statisticsDevice["devicesCount"]);
        $onePercent = ($statisticsDevice["generalInfo"]["countAll"]) / 100;
        $statisticsDevice["devicePercent"] = [];
        foreach($statisticsDevice["devicesCount"] as $device => $countDevice){
            $statisticsDevice["devicePercent"][$device] = number_format($countDevice / $onePercent, 3, '.', '');
        }
        if($parseDevice){
            $statisticsDevice = $this->parseDevice($statisticsDevice);
        }
        if(file_put_contents($this->FileFolderPath.'/statDevice.json', json_encode($statisticsDevice, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false){
            throw new \Exception("Не удалось записать файл statDevice.json");
        }
        return $statisticsDevice;
    }
}
