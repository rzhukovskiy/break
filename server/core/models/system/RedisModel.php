<?php
    /**
     * Базовый класс моделей
     */
    class RedisModel {
        /** @var Globals */
        private $_globals = null;
        /** @var array */
        private $_gameSettings = null;

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
         * @return RedisModel
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
         * Получает подключение к бд с данными
         * @return Rediska
         */
        protected function getDataBase() {
            return $this->_globals->getRediska();
        }

        protected function getValueByKey($key) {
            return $this->getDataBase()->get($key);
        }

        protected function setValueByKey($key, $value) {
            $this->getDataBase()->expire($key, 3600);
            return $this->getDataBase()->set($key, $value);
        }
    }
