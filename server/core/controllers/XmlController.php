<?php
    /**
     * Загрузка xml в бд
     */
    class XmlController extends BaseController {
        //путь до xml
        private $_xmlPath;

        public function __construct() {
            parent::__construct();

            $this->_xmlPath = $this->_globals->getParam('xml_path');
        }

        public function loadAction() {
            $response = new Response();
            $files = scandir($this->_xmlPath);
            $tableCount = 0;
            foreach($files as $filename) {
                $fullFilename = $this->_xmlPath . DIRECTORY_SEPARATOR . $filename;
                if(!is_file($fullFilename) || substr($fullFilename, -3) != 'xml' || $filename == '_files.xml') {
                    continue;
                }

                if(!$this->_createTableFromXmlFile($fullFilename)->isError()) {
                   $tableCount++;
                }
            }

            return $response->setData(array('inserted_tables' => $tableCount))->send();
        }

        /**
         * Создаем таблицу из файла
         * @param $filename string - полное имя файла
         * @return Response
         */
        private function _createTableFromXmlFile($filename) {
            $response = new Response();
            try {
                /** @var $xml SimpleXMLElement */
                $xml = simplexml_load_file($filename);
                $tableName = $xml->getName();
                $tableFields = array();
                foreach($xml->attributes() as $name => $value) {
                    if($name == 'id_col') {
                        continue;
                    }
                    $tableFields[$name] = (string)$value;
                }

                //если не сформировалось списка полей - закругляемся
                if(!$tableFields) {
                    $response->setCode(Response::CODE_WRONG_DATA);
                    $response->setError('Empty table');
                    return $response;
                }
                ksort($tableFields);
                $tableRows = array();
                foreach($xml->children() as $row) {
                    /** @var $row SimpleXMLElement */
                    if($row->getName() != 'row') {
                        continue;
                    }
                    $tableRow = array();
                    foreach($row->children() as $column) {
                        /** @var $column SimpleXMLElement */
                        if(!isset($tableFields[$column->getName()])) {
                            continue;
                        }
                        $tableRow[$column->getName()] = (string)$column;
                    }
                    ksort($tableRow);
                    $tableRows[] = $tableRow;
                }

                //если сформировался массив данных - создаем таблицу
                if($tableRows) {
                    $response = BaseModel::getInstance()->createGameTable($tableName, $tableFields, $tableRows);
                }

                return $response;
            } catch(Exception $ex) {
                $response = new Response();
                $response->setCode(Response::CODE_ERROR)->setError($ex->getMessage());
                return $response;
            }
        }
    }
