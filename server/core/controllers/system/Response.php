<?php
    /**
     * Объект ответа
     */
    class Response {
        const CODE_OK           = 1; //все ок
        const CODE_NOT_AUTH     = 2; //пользователь не авторизован
        const CODE_WRONG_DATA   = 3; //неверные данные (запрос отработал без ошибок, но не изменил бд, так как не выполнилось какое-то условие)
        const CODE_ERROR        = 4; //запрос вызвал ошибку

        /** @var int */
        private $_responseCode = self::CODE_OK;
        /** @var array */
        private $_data = array();
        /** @var string */
        private $_error = '';

        /**
         * Код ответа
         * @param int $code
         * @return Response
         */
        public function setCode($code) {
            $this->_responseCode = $code;
            return $this;
        }

        /**
         * Устанавливаем данные, возвращаемые в ответе
         * @param array $data
         * @return Response
         */
        public function setData($data) {
            $this->_data = $data;
            return $this;
        }

        /**
         * @return array
         */
        public function getData() {
            return $this->_data;
        }

        /**
         * Устанавливаем текст ошибки
         * @param string $error
         * @return Response
         */
        public function setError($error) {
            $this->_error = $error;
            return $this;
        }

        /**
         * Является ли ошибочным. Ошибочный - любой не ок
         * @return bool
         */
        public function isError() {
            return $this->_responseCode != self::CODE_OK;
        }

        /**
         * Преобразуем данные в JSON и отправляем
         */
        public function send() {
            $response = array();
            $response['server_time']    = time();
            $response['server_date']    = date('Y-m-d H:i:s P');
            $response['response_code']  = $this->_responseCode;
            $response['data']           = $this->_data;
            $response['error']          = $this->_error;

            $encodedResponse = json_encode($response);
            echo $encodedResponse;
            exit();
        }
    }

