<?php 

// Класс использует класс Wialon: https://github.com/wialon/php-wialon/blob/master/wialon.php

class WialonHelper{
	
	public $devicesFilePath = __DIR__;
	public $wialonToken = "your_wialon_token";
	
    public function TypeSensor(string $deviceType): string
    {
        switch($deviceType){
            case "absolute fuel consumption": 
                return "Датчик абсолютного расхода топлива"; 
            case "accelerometer":
                return "Акселерометр";
            case "alarm trigger": 
                return "Тревожная кнопка"; 
            case "counter":  
                return "Счетчик"; 
            case "custom":  
                return "Произвольный датчик"; 
            case "digital":  
                return "Произвольный цифровой датчик";
            case "driver":  
                return "Привязка водителя";
            case "engine efficiency":  
                return "Датчик полезной работы двигателя"; 
            case "engine hours":  
                return "Абсолютные моточасы"; 
            case "engine operation":  
                return "Датчик зажигания"; 
            case "engine rpm":  
                return "датчик оборотов двигателя"; 
            case "fuel level impulse sensor":  
                return "Импульсный датчик уровня топлива"; 
            case "fuel level":  
                return "Датчик уровня топлива"; 
            case "impulse fuel consumption":  
                return "Импульсный датчик расхода топлива"; 
            case "instant fuel consumption":  
                return "Датчик мгновенного расхода топлива"; 
            case "mileage":  
                return "Датчик пробега"; 
            case "odometer":  
                return "Относительный одометр"; ;
            case "private mode":  
                return "Частный режим"; 
            case "relative engine hours":  
                return "Относительные моточасы"; 
            case "temperature coefficient":  
                return "Коэффициент температуры"; 
            case "temperature":  
                return "Датчик температуры"; 
            case "trailer":  
                return "Привязка прицепа"; ;
            case "voltage":  
                return "Датчик напряжения"; 
            case "weight sensor":  
                return "Датчик веса"; 
            default:  
                return "not_type";
        }
    }
    
    public function TypeValid(int $num): string
    {
        switch ($num){
            case 1: 
                return "Логическое И"; 
            case 2: 
                return "Логическое ИЛИ"; 
            case 3: 
                return "Математическое И"; 
            case 4: 
                return "Математическое ИЛИ"; 
            case 5: 
                return "Суммировать"; 
            case 6: 
                return "Вычесть валидатор из датчика"; 
            case 7: 
                return "Вычесть датчик из валидатора"; 
            case 8: 
                return "Перемножить"; 
            case 9: 
                return "Делить датчик на валидатор"; 
            case 10: 
                return "Делить валидатор на датчик"; 
            case 11: 
                return "Проверка на неравенство нулю"; 
            case 12: 
                return "Заменять датчик валидатором в случае ошибки"; 
            default: 
                return "not_type";
        }
    }
    
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
    
}
