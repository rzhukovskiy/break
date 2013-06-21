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
                } elseif($filename == 'step.xml') {
                    if(!$this->_createStepTable($fullFilename)->isError()) {
                        $tableCount++;
                    }
                } else {
                    if(!$this->_createTableFromXmlFile($fullFilename)->isError()) {
                        $tableCount++;
                    }
                }
            }

            $response->setData(array('inserted_tables' => $tableCount))->send();
        }

        /**
         * Создаем таблицу для движений из файла
         * @param $filename string - полное имя файла
         * @return Response
         */
        private function _createStepTable($filename) {
            $response = $this->_parseXml($filename);

            $tableData = $response->getData();
            //если сформировался массив данных - создаем таблицу
            if($tableData) {
                foreach($tableData['table_rows'] as &$tableRow) {
                    /** @var $xml SimpleXMLElement */
                    $id = $tableRow['id'];
                    $fullFilename = $this->_xmlPath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'dance_moves' . DIRECTORY_SEPARATOR . $id . '.xml';
                    $stepData = $this->_parseXml($fullFilename);
                    $stepData = $stepData->getData();
                    for($i = 0; $i < count($stepData['table_rows']); $i++) {
                        $level = isset($stepData['table_rows'][$i]['level']) ? $stepData['table_rows'][$i]['level'] : 0;
                        $coins = isset($stepData['table_rows'][$i]['coins']) ? $stepData['table_rows'][$i]['coins'] : 0;
                        $energy = isset($stepData['table_rows'][$i]['energy']) ? $stepData['table_rows'][$i]['energy'] : 0;

                        $tableData['table_fields']['coins_' . $level] = 'int';
                        $tableData['table_fields']['energy_' . $level] = 'int';
                        $tableRow['coins_' . $level] = $coins;
                        $tableRow['energy_' . $level] = $energy;
                    }
                }

                $response = BaseModel::getInstance()->createGameTable($tableData['table_name'], $tableData['table_fields'], $tableData['table_rows']);
            }

            return $response;
        }

        /**
         * Создаем таблицу из файла
         * @param $filename string - полное имя файла
         * @return Response
         */
        private function _createTableFromXmlFile($filename) {
            $response = $this->_parseXml($filename);

            $tableData = $response->getData();
            //если сформировался массив данных - создаем таблицу
            if($tableData) {
                $response = BaseModel::getInstance()->createGameTable($tableData['table_name'], $tableData['table_fields'], $tableData['table_rows']);
            }

            return $response;
        }

        private function _parseXml($filename) {
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
                    return $response->setData(array(
                        'table_name'    => $tableName,
                        'table_fields'  => $tableFields,
                        'table_rows'    => $tableRows
                    ));
                }

                return $response;
            } catch(Exception $ex) {
                $response = new Response();
                $response->setCode(Response::CODE_ERROR)->setError($ex->getMessage());
                return $response;
            }
        }
    }
