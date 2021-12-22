# WialonApiClient
### Класс для работы с Wialon API
___
**`$wialonToken`** - токен доступа к API Wialon  
**`$FileFolderPath`** - путь к папке для хранения файлов, создаваемых классом WialonHelper  
**`$dateFormat`** - формат даты  
**`SENSOR_TYPE`** - Массив типов датчиков в Wialon  
**`VALID_TYPE`** - Массив с кодом и соответствующим значением типов валидации в Wialon
___
#### `searchItems($request, array $options=[])`  
Осуществляет поиск объекта в Wialon по заданным параметрам.  
`$request` - поисковый запрос.  
`$options` - (массив) параметры запроса (необязательный аргумент)  

Если вызывать `searchItems` только с параметром `$request`, то `$options` примет значения по умолчанию:  

`itemsType = avl_unit` - поиск будет производиться по объектам  
`propName = sys_name` - поиск будет производиться по имени объекта  
`sortType = sys_name` - сортировка будет производиться по имени объекта  
`propType = property` - тип: свойство  
`or_logiс = 0`  
`force = 1`  
`flags = 1`  
`from = 0`  
`to = 0`  
  
Подробнее о параметрах поиска вы можете прочесть тут: [Wialon API: Поиск элементов](https://sdk.wialon.com/wiki/ru/local/remoteapi2004/apiref/core/search_items)  

Массив `$options` не имеет вложенных массивов! Все параметры находятся на одном уровне.
___
#### `getObjectData($objectId)`  
Вернёт сырой массив данных объекта.  
`$objectId` - id объекта в Wialon.  
Использует класс Wialon.  
___
#### `ShortInfo(int $objectId, bool $lastMessage = false, bool $sensors = false)`  
Получает информацию об объекте методом `getObjectData` и обрабатывает её.  
Возвращает массив.  
`$objectId` (число) - id объекта в Wialon.  
`$lastMessage` (true/false) - Возвращать в массиве последнее сообщение объекта  
`$sensors` (true/false) - Возвращать в массиве датчики объекта  
`$tableRaw` (true/false) - Возвращать в массиве датчика таблицу расчёта  
___
#### `processingArraySensors(array $rawSensors, $lastMessage = [], bool $tableRaw = false)`  
`$rawSensors` - Массив датчиков объекта  
`$lastMessage` - Последнее сообщение (массив, необязательный параметр).  
`$tableRaw` (true/false) - Возвращать в массиве датчика таблицу расчёта  
Если будет передано последнее сообщение то возвращаемый массив будет содержать ключ `lastVal`, с последним значением параметра, используемого датчиком.  
Ключ `lastVal` будет в возвращаемом массиве только в случае если парамет для датчика задан напрямую, без квадратных скобок и выражений.  
___
#### `sensorProcessing(array $rawSensor, bool $tableRaw = false)`  
Возвращает массив с обработанной информацией датчика.  
`$rawSensor` (массив) - сырой массив датчика.  
`$tableRaw` (true/false) - Возвращать в массиве датчика таблицу расчёта  
Определит тип датчика и тип валидации, при наличии валидации.    
___
#### `getAllDevices()`  
Запрашивает у Wialon поддерживаемые устройства и сохраняет их в файл _devices.json_.  
Использует класс Wialon  
___
#### `deviceName(int $device_id)`  
Вернёт название устройства (строку) по его коду в Wialon.  
`$device_id` (число) - код устройства в Wialon.  
Использует файл _devices.json_, создаваемый методом `getAllDevices`.  
Вызовет метод `getAllDevices` для создания файла _devices.json_, если не найдёт файл в дирректории `FileFolderPath`.  
___
#### `getAllGroups()`  
Получение всех доступных групп и запись их в файл _groups.json_.  
Возвращает массив полученных групп.  
Использует класс Wialon.  
___
#### `objectGroups(int $objectId)`  
Вернёт массив групп объекта из файла _groups.json_  
`$objectId (число)` - id объекта в Wialon.  
Использует файл _groups.json_, создаваемый методом objectGroups.  
Вызовет метод `objectGroups` для создания файла _groups.json_, если не найдёт файл в дирректории `FileFolderPath`.  
___
#### `sensorType(string $deviceType)`  
Возвращает тип датчика (строку).  
`$deviceType` (строка) тип датчика в Wialon.  
Использует массив `SENSOR_TYPE`.  
Вернёт **not_type** если тип не был найден.  
___
#### `validType(int $num)`  
Возвращает тип валидации датчика (строку).  
`$num` - (число) код валидации в Wialon.  
Использует массив `VALID_TYPE`.  
Вернёт **not_type** если тип не был найден.  
