<?php
use Bitrix\Main;
use Bitrix\Main\Context;
Main\EventManager::getInstance()->addEventHandler(
    'form',
    'onAfterResultAdd',
    ["ExtCrmExchange", "handler"]
);

// Класс отвечает за обновление полей результатов форм, которые передаются в CRM
class ExtCrmExchange
{
    /**
     * @var ExtCrmExchange
     */
    private static $instance;

    /**
     * @var CFormResult
     */
    private $cFormResult;

    /**
     * ID веб форм, которые будут обновляться
     *
     * @var array
     */
    protected $formIds = [3 , 4, 5];

    /**
     * Подключаемые модули
     *
     * @var array
     */
    private $modules = ["form"];

    /**
     * Обработчик. Срабатывает после добавления нового результата веб-формы
     *
     * @param integer|string $webFormId
     * @param integer|string $resultId
     *
     * @return void
     */
    public function handler($webFormId, $resultId)
    {
        // Так как при вызове метода не создается объект класса создаем сами
        $exchange = self::getInstance();

        if($exchange->includeModules()) {
            // Создаем объект класса CFormResult
            $exchange->createForm();

            $needleId = intval($webFormId);
            if(in_array($needleId, $exchange->formIds, true)) {
                $fieldsRes = $exchange->cFormResult->GetDataByID($resultId, [], $result, $answer);

                $arrayUpdate = []; // Массив для передачи в метод CFormResult::Update
                $pathToFiles = []; // Путь к файлам для скачивания в CRM
                foreach ($fieldsRes as $fieldKey => $fieldName) {
                    $formKey = "form_". $fieldName[0]["FIELD_TYPE"]. "_" . $fieldName[0]["ANSWER_ID"];

                    switch ($fieldName[0]["FIELD_TYPE"]) {
                        case "text":
                        case "textarea":
                            if(!empty($fieldName[0]["USER_TEXT"])) {
                                $arrayUpdate[$formKey] = $fieldName[0]["USER_TEXT"];
                            }
                            break;

                        case "image":
                        case "file":
                            if(!empty($fieldName[0]["USER_FILE_HASH"])) {
                                // TODO метод GetFileByHash проверяет права на форму.
                                // Необходимо изменять права при использовании метода на более высокие 25 и потом возвращать обратно
                                $fileInfo = $exchange->cFormResult->GetFileByHash($resultId, $fieldName[0]["USER_FILE_HASH"]);
                                $path = CFile::GetPath($fileInfo["FILE_ID"]);
                                array_push($pathToFiles, $path);
                            }
                            break;
                    }
                }

                // Проходим еще раз чтобы добавить ссылку на файл для передачи в CRM.
                $countFile = 0;
                foreach ($fieldsRes as $fieldKey => $fieldName) {
                    $formKey = "form_". $fieldName[0]["FIELD_TYPE"]. "_" . $fieldName[0]["ANSWER_ID"];

                    switch ($fieldName[0]["FIELD_TYPE"]) {
                        case "url":
                            $arrayUpdate[$formKey] = $exchange->getCurrentUrl() . $pathToFiles[$countFile];
                            $countFile++;
                            break;
                    }
                }

                // Удаляем файлы, чтобы корректно передавалась ссылка на файл иначе метод update добавит новый файл
                $exchange->removeFiles();

                $exchange->update($resultId, $arrayUpdate);
            }
        }
    }

    /**
     * Возвращает объект текущего класса если он не создан
     *
     * @return ExtCrmExchange
     */
    protected static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Создает объект CFormResult
     *
     * @return void
     */
    protected function createForm()
    {
        if(empty($this->cFormResult)) {
            $this->cFormResult = new CFormResult();
        }
    }

    /**
     * Подключает необходимые модули
     *
     * @return bool
     */
    private function includeModules()
    {
        foreach ($this->modules as $module) {
            if(!CModule::IncludeModule($module)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Очищает переменную с файлами $_FILES
     */
    private function removeFiles()
    {
        if(!empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                unset($_FILES[$key]);
            }
        }
    }

    /**
     * Обновляет результаты формы в соответствии с массивом $fields
     *
     * @param integer|string $resultId
     * @param array $fields
     *
     * @return void
     */
    protected function update($resultId, $fields)
    {
        $this->cFormResult->Update($resultId, $fields, "N", "N");
    }

    /**
     * Возвращает текущий урл сайта http://sitename.ru или https://sitename.ru
     *
     * @return string
     */
    protected function getCurrentUrl()
    {
        $server = Context::getCurrent()->getServer();
        $host = $server->get("HTTP_HOST");
        $sheme = (Context::getCurrent()->getRequest()->isHttps()) ? "https" : "http" ;
        $url = $sheme . "://" . $host;

        return $url;
    }
}

// $eventManager = \Bitrix\Main\EventManager::getInstance();
 
// $eventManager->addEventHandler("main", "OnBeforeEventAdd", array("MailEventHandler", "onBeforeEventAddHandler"));
// class MailEventHandler{
 
//     static function onBeforeEventAddHandler(&$event, &$lid, &$arFields, &$message_id, &$files){
//         // Названия типов почтовых событий и идентификаторы почтовых шаблонов, по которым будет проходить фильтрация при отлавливании события
//         $events = array('FORM_FILLING_SIMPLE_FORM_4', 'FORM_FILLING_SIMPLE_FORM_3');
//         $messageIds = array('40', '39');
//         if (in_array($event, $events) && in_array($message_id, $messageIds)){
//             // Определяем массив прикрепляемых к письму идентификаторов файлов, если не задан
//             if (!is_array($files)){
//                 $files = [];
//             }
//             // Перебираем поля письма
//             foreach ($arFields as $field){
//                 // Если находим ссылку на файл, передаем ее дальше
//                 if ($link = self::getLinkFromField($field)){
//                     // Если получаем идентификатор файла, сохраняем его в массив идентификаторов файлов письма
//                     if ($fileID = self::getFileFromLink($link)){
//                         $files[] = $fileID;
//                     }
//                 }
//             }
//         }
//     }
 
//     // Метод возвращает ссылку на файл
//     private static function getLinkFromField($field){
// 		preg_match("/(http[s]?\:.*form_show_file.*action\=download)/", $field, $out);
//         // Если поле соответствует маске пути к файлу, возвращаем путь
//         return ($out[1] ?: false);
//     }
//     // Метод возвращает идентификатор файла
//     private static function getFileFromLink($link){
// 		CModule::IncludeModule('form');
//         // Создаем новый объект, в который записываем ссылку
// 		$uri = new \Bitrix\Main\Web\Uri($link);
//         // Разбираем строку URI и создаем массив из параметров запроса
// 		parse_str($uri->getQuery(), $query);
//         // Метод возвращает массив свойств файла по идентификатору результата веб-формы и хэшу
		
// 		global $DB;

// 		$RESULT_ID = intval($query['rid']);
// 		$HASH = $query['hash'];
		
// 		$strSql = "
// 		SELECT
// 			F.ID as FILE_ID,
// 			F.FILE_NAME,
// 			F.SUBDIR,
// 			F.CONTENT_TYPE,
// 			F.HANDLER_ID,
// 			F.FILE_SIZE,
// 			RA.USER_FILE_NAME ORIGINAL_NAME,
// 			RA.USER_FILE_IS_IMAGE,
// 			RA.FORM_ID, R.USER_ID
// 		FROM b_form_result R
// 		LEFT JOIN b_form_result_answer RA ON RA.RESULT_ID=R.ID
// 		INNER JOIN b_file F ON (F.ID = RA.USER_FILE_ID)
// 		WHERE R.ID = '".$RESULT_ID."'
// 		AND RA.USER_FILE_HASH = '".$DB->ForSql($HASH, 255)."'
// 		";
		
// 		//echo $strSql;
		
// 		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
// 		$arFile = $z->Fetch();
//         // Возвращаем идентификатор файла
//         return $arFile['FILE_ID'];
//     }
// }