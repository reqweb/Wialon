<?php 

// WialonHelper использует класс Wialon: https://github.com/wialon/php-wialon/blob/master/wialon.php

class WialonHelper{
    
    private $wialonToken = "your_wialon_token";
    
    public $FileFolderPath = __DIR__;
    public $dateFormat = "d.m.Y H:i";
    
    const SENSOR_TYPE = [
        'absolute fuel consumption' => 'Датчик абсолютного расхода топлива', 
        'accelerometer' => 'Акселерометр',
        'alarm trigger' => 'Тревожная кнопка', 
        'counter' => 'Счетчик', 
        'custom' => 'Произвольный датчик', 
        'digital' => 'Произвольный цифровой датчик',
        'driver' => 'Привязка водителя',
        'engine efficiency' => 'Датчик полезной работы двигателя', 
        'engine hours' => 'Абсолютные моточасы', 
        'engine operation' => 'Датчик зажигания', 
        'engine rpm' => 'датчик оборотов двигателя', 
        'fuel level impulse sensor' => 'Импульсный датчик уровня топлива', 
        'fuel level' => 'Датчик уровня топлива', 
        'impulse fuel consumption' => 'Импульсный датчик расхода топлива', 
        'instant fuel consumption' => 'Датчик мгновенного расхода топлива', 
        'mileage' => 'Датчик пробега',
        'odometer' => 'Относительный одометр',
        'private mode' => 'Частный режим', 
        'relative engine hours' => 'Относительные моточасы', 
        'temperature coefficient' => 'Коэффициент температуры', 
        'temperature' => 'Датчик температуры', 
        'trailer' => 'Привязка прицепа',
        'voltage' => 'Датчик напряжения', 
        'weight sensor' => 'Датчик веса'
    ];
    
    const VALID_TYPE = [
        '1' => 'Логическое И', 
        '2' => 'Логическое ИЛИ', 
        '3' => 'Математическое И', 
        '4' => 'Математическое ИЛИ', 
        '5' => 'Суммировать', 
        '6' => 'Вычесть валидатор из датчика', 
        '7' => 'Вычесть датчик из валидатора', 
        '8' => 'Перемножить', 
        '9' => 'Делить датчик на валидатор', 
        '10' => 'Делить валидатор на датчик', 
        '11' => 'Проверка на неравенство нулю', 
        '12' => 'Заменять датчик валидатором в случае ошибки'
    ];
    
    // Поиск
    public function searchItems($request, array $options=[]): array
    {
        $requestParameters["spec"]["propValueMask"] = "*".trim($request, "\*\ ")."*";
        
        isset($options["itemsType"]) 
            ? $requestParameters["spec"]["itemsType"] = $options["itemsType"] 
            : $requestParameters["spec"]["itemsType"] = "avl_unit";
        isset($options["propName"]) 
            ? $requestParameters["spec"]["propName"] = $options["propName"] 
            : $requestParameters["spec"]["propName"] = "sys_name";
        isset($options["sortType"]) 
            ? $requestParameters["spec"]["sortType"] = $options["sortType"] 
            : $requestParameters["spec"]["sortType"] = "sys_name";
        isset($options["propType"]) 
            ? $requestParameters["spec"]["propType"] = $options["propType"] 
            : $requestParameters["spec"]["propType"] = "property";
        isset($options["or_logic"]) 
            ? $requestParameters["spec"]["or_logic"] = $options["or_logic"] 
            : $requestParameters["spec"]["or_logic"] = "0";
        
        isset($options["force"]) 
            ? $requestParameters["force"] = $options["force"] 
            : $requestParameters["force"] = "1";
        isset($options["flags"]) 
            ? $requestParameters["flags"] = $options["flags"] 
            : $requestParameters["flags"] = "1";
        isset($options["from"]) 
            ? $requestParameters["from"] = $options["from"] 
            : $requestParameters["from"] = "0";
        isset($options["to"]) 
            ? $requestParameters["to"] = $options["to"] 
            : $requestParameters["to"] = "0";
        
        $Wialon = new Wialon();
        $result = $Wialon->login($this->wialonToken);
        $json = json_decode($result, true);

        if(isset($json['error'])){
            throw new \Exception(WialonError::error($json['error']));
        }
        $jsonstring = $Wialon->core_search_items(json_encode($requestParameters));
        $Wialon->logout();
        return json_decode($jsonstring, true);
    }
    
    // Запрос данных объекта (со всеми возможными флагами)
    public function getObjectData(int $objectId): array
    {
        $Wialon = new Wialon();
        $result = $Wialon->login($this->wialonToken);
        $json = json_decode($result, true);

        if(isset($json['error'])){
            throw new \Exception(WialonError::error($json['error']));
        }
        $jsonstring = $Wialon->core_search_item('{
            "id":"'.$objectId.'",
            "flags":4611686018427387903
        }');
        $Wialon->logout();
        return json_decode($jsonstring, true);
    }
    
    // Обрабатывает массив полученный от getObjectData, возвращая информацию о объекте
    public function ShortInfo(
        int $objectId, // id объекта
        bool $lastMessage = false, // Возвращать в массиве последнее сообщение объекта
        bool $sensors = false // Возвращать в массиве датчики объекта
    ): array
    {
        $parsedObject = [];
        $rawObject = $this->getObjectData($objectId);
        
        if(isset($rawObject["item"]["netconn"])){
            $parsedObject["info"]["connect"] = $rawObject["item"]["netconn"];
        }
        $parsedObject["info"]["name"] = $rawObject["item"]["nm"];
        if(isset($rawObject["item"]["ct"])){
            $parsedObject["info"]["create_time"] = date($this->dateFormat, $rawObject["item"]["ct"]); 
        }
        if(isset($rawObject["item"]["hw"])){
            $parsedObject["info"]["device"] = $this->deviceName($rawObject["item"]["hw"]);
        }
        if(isset($rawObject["item"]["uid"])){
            $parsedObject["info"]["device_imei"] = $rawObject["item"]["uid"];
        }
        if(isset($rawObject["item"]["ph"])){
            $parsedObject["info"]["phone"] = $rawObject["item"]["ph"];
        }
        $parsedObject["info"]["groups"] = $this->objectGroups($objectId);
        if(isset($rawObject["item"]["lmsg"]["t"])){
            $parsedObject["info"]["last_message_time_unix"] = $rawObject["item"]["lmsg"]["t"];
            $parsedObject["info"]["last_message_time"] = date($this->dateFormat, $rawObject["item"]["lmsg"]["t"]);
        }
        if($lastMessage && isset($rawObject["item"]["lmsg"])){
            $parsedObject["last_message"] = $rawObject["item"]["lmsg"];
        }
        if($sensors && isset($rawObject["item"]["sens"])){
            $parsedObject["sensors"] = $this->processingArraySensors($rawObject["item"]["sens"], $parsedObject["last_message"]);
        }
        return $parsedObject;
    }
    
    // Обработка датчиков объекта
    public function processingArraySensors(array $rawSensors, $lastMessage = []): array
    {
        $parsedSensors = [];
        if(count($rawSensors) > 0){
            foreach($rawSensors as $sensor){
                $parsedSensors[$sensor["id"]] = $this->sensorProcessing($sensor);
                if(count($lastMessage) > 0){
                    if($parsedSensors[$sensor["id"]]["paramType"] == "real" && !isset($parsedSensors[$sensor["id"]]["tableRaw"])){
                        $parsedSensors[$sensor["id"]]["lastVal"] = $lastMessage["p"][$parsedSensors[$sensor["id"]]["param"]];
                    }
                }
            } 
        }
        return $parsedSensors;
    }
    
    // Обработка датчикa
    public function sensorProcessing(array $rawSensor): array
    {
        $parsedSensor = [];
        $parsedSensor["id"] = $rawSensor["id"];
        $parsedSensor["name"] = $rawSensor["n"];
        $parsedSensor["type"] = $this->SensorType($rawSensor["t"]);
        if(!empty($rawSensor["m"])){
            $parsedSensor["m"] = $rawSensor["m"];
        }
        $paramDescParts = explode("|", $rawSensor["d"]);
        if(!empty($paramDescParts[0])){
            $parsedSensor["desc"] = $paramDescParts[0];
        }
        $parsedSensor["param"] = $rawSensor["p"];
        
        if(strpos($rawSensor["p"], "[") === false){
            $parsedSensor["paramType"] = "real";
        }else{
            $parsedSensor["paramType"] = "virtual";
        }
        if(!empty($rawSensor["vs"]) && $rawSensor["vs"] != 0){
            $parsedSensor["validSensor"] = $rawSensor["vs"];
            $parsedSensor["validType"] =  $this->ValidType($rawSensor["vt"]);
        }
        if(!empty($paramDescParts[1])){
            $tableRaw = explode(":", $paramDescParts[1]);
            $parsedSensor["tableRaw"] = [];
            $count = 0;
            $tmpKey = 0;
            foreach($tableRaw as $val){
                $count++;
                if(fmod($count, 2) == 0){
                    $parsedSensor["tableRaw"][$tmpKey] = $val;
                }else{
                    $parsedSensor["tableRaw"][$val] = "";
                    $tmpKey = $val;
                }
            }
        }
        return $parsedSensor;
    }

    // Запрос поддерживаемых устройств Wialon и запись в файл 
    public function getAllDevices(): array
    {
        $Wialon = new Wialon();
        $result = $Wialon->login($this->wialonToken);
        $json = json_decode($result, true);
        if(isset($json['error'])){
            throw new \Exception(WialonError::error($json['error']));
        }
        $jsonstring = $Wialon->core_get_hw_types('{
            "filterType":"name",
            "includeType":1
        }');
        $string_array = json_decode($jsonstring, true);
        $Wialon->logout();
        $devicesAsArray = [];
        $devicesAsArray["time"] = date($this->dateFormat, time());
        foreach($string_array as $item){
            if(is_array($item)){
                $devicesAsArray["devices"][$item["id"]] = $item["name"];
            }
        }
        if(file_put_contents($this->FileFolderPath.'/devices.json', json_encode($devicesAsArray, JSON_PRETTY_PRINT)) === false){
            throw new \Exception("Не удалось записать файл");
        }
        return $devicesAsArray["devices"];
    }
    
    // Определение устройства по id
    public function deviceName(int $device_id): string
    {
        if(!file_exists($this->FileFolderPath.'/devices.json')){
            $this->getAllDevices();
        }
        $devices_array = json_decode(file_get_contents($this->FileFolderPath.'/devices.json'), true);
        if(array_key_exists($device_id, $devices_array["devices"])){
            return $devices_array["devices"][$device_id];
        }else{
            return "unknown";
        }
    }
    
    // Получение всех групп и запись в файл
    public function getAllGroups(): array
    {
        $Wialon = new Wialon();
        $result = $Wialon->login($this->wialonToken);
        $json = json_decode($result, true);
        if(isset($json['error'])){
            throw new \Exception(WialonError::error($json['error']));
        }
        $jsonstring = $Wialon->core_search_items('{
            "spec":{
                "itemsType":"avl_unit_group",
                "propName":"sys_id",
                "propValueMask":"*",
                "sortType":"sys_name"
            },
                "force":1,
                "flags":1,
                "from":0,
                "to":0
        }');
        $groupsAsArray = json_decode($jsonstring, true);
        $Wialon->logout();
        $groupsAsArray["time"] = date($this->dateFormat, time());
        if(file_put_contents($this->FileFolderPath.'/groups.json', json_encode($groupsAsArray, JSON_PRETTY_PRINT)) === false){
            throw new \Exception("Не удалось записать файл");
        }
        return $groupsAsArray;
    }
    
    // Вернёт группы объекта по id (объекта)
    public function objectGroups(int $objectId): array
    {
        if(!file_exists($this->FileFolderPath.'/groups.json')){
            $this->getAllGroups();
        }
        $groupsArray = json_decode(file_get_contents($this->FileFolderPath.'/groups.json'), true);
        $objectGroups = [];
        if(count($groupsArray) > 0){
            foreach($groupsArray["items"] as $group){
                if(array_search($objectId, $group["u"]) === false){
                    
                }else{
                    $objectGroups[] = $group["nm"];
                }
            }
        }
        return $objectGroups;
    }
    
    // Тип датчика
    public function SensorType(string $deviceType): string
    {
        if(array_key_exists($deviceType, self::SENSOR_TYPE)){
            return self::SENSOR_TYPE[$deviceType];
        }else{
            return "not_type";
        }
    }
    
    // Тип валидации датчика
    public function ValidType(int $num): string
    {
        if(array_key_exists($num, self::VALID_TYPE)){
            return self::VALID_TYPE[$num];
        }else{
            return "not_type";
        }
    }
    
}
