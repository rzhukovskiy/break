<?php
    /**
     * Глобальные параметры и настройки
     */
    final class Globals {
        const ERROR_HANDLING_PARAM      = 'error_handling';
        const TEST_MODE_PARAM           = 'test_mode';
        const TEST_MODE_USER_PARAM      = 'test_mode_user';
        const REQUEST_TIMEOUT_PARAM     = 'request_timeout';
        const INTERNAL_KEY_PARAM        = 'internal_key';
        const SITE_PATH_PARAM           = 'site_path';
        const DB_CONFIG_PARAM           = 'db_config';
        const REDIS_CONFIG_PARAM        = 'redis_config';
        const PLATFORM_PARAM            = 'platform';
        const DEFAULT_PLATFORM_PARAM    = 'default_platform';

        /** @var array */
        private $_data = array();
        /** @var Globals */
        private static $_instance;
        /** @var MongoDb */
        private $_gameBase;
        /** @var MongoDb */
        private $_dataBase;
        /** @var Rediska */
        private $_rediska;
        /** @var Error */
        private $_error;
        /** @var Http */
        private $_http;

        private function __construct() {
            $this->_error = new Error();
            $this->_http = new Http();
        }

        /**
         * Создание экземпляра
         * @return Globals
         */
        public static function init() {
            if (empty(self::$_instance)) {
                self::$_instance = new Globals();
                return self::$_instance;
            } else {
                return self::$_instance;
            }
        }

        /**
         * Установить параметр
         * @param string $key
         * @param $val
         * @return Globals
         */
        public function set($key, $val) {
            $this->_data[$key] = $val;
            return $this;
        }

        /**
         * Удалить параметр
         * @param string $key
         */
        public function remove($key) {
            if (isset($this->_data[$key])) {
                unset($this->_data[$key]);
            }
        }

        /**
         * Получить класс обработчик ошибок
         * @return Error
         */
        public function getError() {
            return $this->_error;
        }

        /**
         * Получить класс работы с HTTP
         * @return Http
         */
        public function getHttp() {
            return $this->_http;
        }

        /**
         * Получить параметр. Если параметр не установлен - вернуть значение $default
         * @param string $name
         * @param mixed $default
         * @return null|string
         */
        public function getParam($name, $default = null) {
            if (isset($this->_data[$name])) {
                return $this->_data[$name];
            } elseif ($default != null) {
                return $default;
            } else {
                return null;
            }
        }

        /**
         * Получает текущую соц.сеть
         * return string
         */
        public function getPlatform() {
            return $this->getParam(self::PLATFORM_PARAM, self::DEFAULT_PLATFORM_PARAM);
        }

        /**
         * Тестовый ли режим
         * @return bool
         */
        public function isTestMode() {
            return $this->getParam(self::TEST_MODE_PARAM, false);
        }

        /**
         * Создает подключение к бд и сохраняет его
         */
        public function setDbConnection() {
            $params = $this->getParam(self::DB_CONFIG_PARAM);
            //для mysql
            if($params['driver'] == "mysql") {
                $this->_dataBase = new PDO('mysql:host='.$params['host'].';dbname='.$params['db_name'].'', $params['user'], $params['password']);
                $this->_gameBase = new PDO('mysql:host='.$params['host'].';dbname='.$params['db_name'].'', $params['user'], $params['password']);
            }
            //для mongo
            if($params['driver'] == "mongodb") {
                $mongodb = "mongodb://";

                if(isset($params['user']) && isset($params['password']))
                    $mongodb .= "{$params['user']}:{$params['password']}@";

                $mongodb .= "{$params['host']}";
                if(isset($params['port']))
                    $mongodb .= ":{$params['port']}";

                if(isset($config['pool_size']))
                    MongoPool::setSize($params['pool_size']);

                $connection = new Mongo($mongodb);
                $this->_gameBase = $connection->selectDB($params['game_base']);
                $this->_dataBase = $connection->selectDB($params['data_base']);
            }
        }

        /**
         * @return Rediska
         */
        public function getRediska() {
            if(!$this->_rediska) {
                $params = $this->getParam(self::REDIS_CONFIG_PARAM);
                $this->_rediska = new Rediska($params);
            }

            return $this->_rediska;
        }

        /**
         * Подключение к базе с настройками игры
         * @return PDO|MongoDb
         */
        public function getGameBase() {
            return $this->_gameBase;
        }

        /**
         * Подключение к БД с данными игроков
         * @return PDO|MongoDb
         */
        public function getDataBase() {
            return $this->_dataBase;
        }
    }
