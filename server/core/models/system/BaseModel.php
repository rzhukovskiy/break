<?php
    /**
     * Базовый класс моделей
     */
    class BaseModel {
        /** @var Globals */
        private $_globals = null;
        /** @var array */
        private $_gameSettings = null;
        /** @var string */
        protected $_table;

        /**
         * Collection of instances
         *
         * @var array
         */
        private static $_instances = array();

        private function __construct() {
            $this->_globals = Globals::init();
        }

        /**
         * Получить экземпляр
         * @return BaseModel
         */
        public static function getInstance() {
            // Get name of current class
            $sClassName = get_called_class();

            // Create new instance if necessary
            if (! isset(self::$_instances[$sClassName])) {
                self::$_instances[$sClassName] = new $sClassName();
            }

            return self::$_instances[$sClassName];
        }

        /**
         * Получает подключение к бд с данными игры
         * @return PDO|MongoDb
         */
        protected function getGameBase() {
            return $this->_globals->getGameBase();
        }

        /**
         * Получает подключение к бд с данными игроков
         * @return PDO|MongoDb
         */
        protected function getDataBase() {
            return $this->_globals->getDataBase();
        }

        /**
         * Получаем настройки
         * @return array
         */
        protected function getSettingList() {
            if(!$this->_gameSettings) {
                /** @var $gameDb PDO */
                $gameDb = $this->getGameBase();

                $sql = 'SELECT id, value FROM setting';
                $query = $gameDb->prepare($sql);
                $query->execute();

                $this->_gameSettings = $query->fetchAll(PDO::FETCH_KEY_PAIR);
            }

            return $this->_gameSettings;
        }

        /**
         * Получить сущность по ID
         * @param string $id
         * @return Response
         */
        public function getEntityByEntityId($id) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT * FROM ' . $this->_table . ' WHERE id = :id LIMIT 1';
            $query = $gameDb->prepare($sql);
            $query->execute(array(
                ':id' => $id
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($query->fetch(PDO::FETCH_ASSOC));
            }
            return $response;
        }

        /**
         * Удалить сущность
         * @param int $userId
         * @param string $userItemId
         * @return Response
         */
        public function removeEntityById($userId, $userItemId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'DELETE FROM
                    ' . $this->_table . '
                WHERE
                    id = :id AND
                    user_id = :user_id';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':id'           => $userItemId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }

        /**
         * Получить сущность по ID
         * @param string $list
         * @return Response
         */
        public function getEntityListByEntityList($list) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT * FROM ' . $this->_table . ' WHERE id IN ("' . implode('","', $list) . '")';
            $query = $gameDb->prepare($sql);
            $query->execute();

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($query->fetchAll(PDO::FETCH_ASSOC));
            }
            return $response;
        }

        /**
         * Создает таблицу в игровой базе
         * @param $name string - имя таблицы
         * @param $fields array - поля таблицы (имя => тип)
         * @param $rows array - вставляемые строки
         * @return Response
         */
        public function createGameTable($name, $fields, $rows) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();

            $response = new Response();

            //удаление существующей таблицы
            $deleteSql = 'DROP TABLE IF EXISTS `' . $name . '`';

            //создание новой
            $createSql = 'CREATE TABLE `' . $name . '` (';
            foreach($fields as $fieldName => $fieldType) {
                $createSql .= '`' . $fieldName . '` ' . ($fieldType == 'string' ? 'varchar(255)' : $fieldType) . ' NOT NULL,';
            }
            if(isset($fields['id'])) {
                $createSql .= ' PRIMARY KEY (`id`)';
            } else {
                $createSql = substr($createSql, 0, strlen($createSql) - 1);
            }
            $createSql .= ' ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

            //вставка значений
            $insertSql = array();
            foreach($rows as $row) {
                $sqls = array();
                foreach($fields as $fieldName => $fieldType) {
                    if(!isset($row[$fieldName]) && $fieldType == 'string') {
                        $sqls[] = '""';
                    }
                    else if(!isset($row[$fieldName])) {
                        $sqls[] = 0;
                    }
                    else if($fieldType == 'string') {
                        $sqls[] = '"' . $row[$fieldName] . '"';
                    } else {
                        $sqls[] = $row[$fieldName];
                    }
                }
                $insertSql[] = '(' . implode(',', $sqls) . ')';
            }
            $insertSql = 'INSERT INTO `' . $name . '` VALUES ' . implode(',', $insertSql);

            $query = $gameDb->prepare($deleteSql);
            $query->execute();
            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
                return $response;
            }

            $query = $gameDb->prepare($createSql);
            $query->execute();
            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
                return $response;
            }

            $query = $gameDb->prepare($insertSql);
            $query->execute();
            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
                return $response;
            }

            return $response;
        }
    }
