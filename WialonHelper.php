<?php 

// WialonHelper использует класс Wialon: https://github.com/wialon/php-wialon/blob/master/wialon.php

class WialonHelper{
	
    public $devicesFilePath = __DIR__;
    public $wialonToken = "your_wialon_token";
	
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
        if(!isset($options["itemsType"])){$options["spec"]["itemsType"] = "avl_unit";}
        if(!isset($options["propName"])){$options["spec"]["propName"] = "sys_name";}
        $options["spec"]["propValueMask"] = "*".trim($request, "\*\ ")."*";
        if(!isset($options["sortType"])){$options["spec"]["sortType"] = "sys_name";}
        if(!isset($options["propType"])){$options["spec"]["propType"] = "property";}
        if(!isset($options["or_logic"])){$options["spec"]["or_logic"] = "0";}
        if(!isset($options["force"])){$options["force"] = "1";}
        if(!isset($options["flags"])){$options["flags"] = "1";}
        if(!isset($options["from"])){$options["from"] = "0";}
        if(!isset($options["to"])){$options["to"] = "0";}

        $Wialon = new Wialon();
        $result = $Wialon->login($this->wialonToken);
        $json = json_decode($result, true);

        if(isset($json['error'])){
            throw new \Exception(WialonError::error($json['error']));
        }

        $jsonstring = $Wialon->core_search_items(json_encode($options));
        $searchResult = json_decode($jsonstring, true);
        $Wialon->logout();
        return $searchResult;
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
        $time_now = date("d.m.Y H:i", time()+10800);
        $devicesAsArray["time"] = $time_now;
        foreach($string_array as $item){
            if(is_array($item)){
                $devicesAsArray["devices"][$item["id"]] = $item["name"];
            }
        }
        if(file_put_contents($this->devicesFilePath.'/devices.json', json_encode($devicesAsArray, JSON_PRETTY_PRINT)) === false){
            throw new \Exception("Не удалось записать файл");
        }
        return $devicesAsArray["devices"];
    }
    
    // Определение устройства по id
    public function deviceName(int $device_id): string
    {
        if(!file_exists($this->devicesFilePath.'/devices.json')){
            $this->getAllDevices();
        }
        $devices_array = json_decode(file_get_contents($this->devicesFilePath.'/devices.json'), true);
        if(array_key_exists($device_id, $devices_array["devices"])){
            return $devices_array["devices"][$device_id];
        }else{
            return "unknown";
        }
    }
	
    // Тип датчика
    public function SensorType(string $deviceType): string
    {
        if(array_key_exists($deviceType, $this->SENSOR_TYPE)){
            return $this->SENSOR_TYPE[$deviceType];
        }else{
            return "not_type";
        }
    }
    
    // Тип валидации датчика
    public function ValidType(int $num): string
    {
        if(array_key_exists($num, $this->VALID_TYPE)){
            return $this->VALID_TYPE[$deviceType];
        }else{
            return "not_type";
        }
    }
    
}
