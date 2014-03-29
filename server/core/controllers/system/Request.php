<?php
    /**
     * Объект запроса
     */
    class Request {
        const ACCESS_KEY_PARAM  = 'access_key';
        const TIMESTAMP_PARAM   = 'ts';
        const USER_ID_PARAM     = 'viewer_id';

        /** @var array */
        private $_data;
        /** @var Globals */
        private $_settings;

        /**
         * Конструктор. Склеивает полученные гет и пост массивы и записывает в _data
         */
        public function __construct() {
            $data = array_merge($_POST, $_GET, $_REQUEST);
            if($data) {
                $this->_data = $data;
            } else {
                $this->_data = array();
            }

            $this->_settings = Globals::init();
        }

        /**
         * Получить параметр по имени или установить дефолтное значение, если оно передано и параметр не установлен
         * @param string $name
         * @param null $default
         * @return null
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
         * Возвращет парметры запроса как массив
         * @return array
         */
        public function toArray() {
            return $this->_data;
        }

        /**
         * Проверка запроса на валидность
         * Проверяет подпись. В тестовом режиме всегда возращает true
         * @return bool
         */
        public function getStatus() {
            if ($this->_settings->isTestMode()) {
                return true;
            } else {
                $reserved = array('ts', 'access_key', 'nickname');
                ksort($this->_data);
                $data = '';
                foreach($this->_data as $key => $value) {
                    if(in_array($key, $reserved)) {
                        continue;
                    }
                    $data .= $key . $value;
                }

                $accessKey      = $this->getParam(self::ACCESS_KEY_PARAM);
                $internalKey    = $this->_settings->getParam(Globals::INTERNAL_KEY_PARAM);
                $requestTimeout = $this->_settings->getParam(Globals::REQUEST_TIMEOUT_PARAM);
                $ts             = $this->getParam(self::TIMESTAMP_PARAM);

                //Если пришел флешовый таймстамп, надо поделить на тыщу
                if($ts >= 10000000000) {
                    $time = $ts / 1000;
                } else {
                    $time = $ts;
                }

                if($accessKey != md5($internalKey . md5($internalKey . $ts . $data))) {
                    return Response::CODE_NOT_AUTH;
                }
                return Response::CODE_OK;
            }
        }
    }
