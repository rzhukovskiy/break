<?php
    /**
     * Базовый контроллер. Остальные наследуются от него
     */
    class BaseController {
        /** @var Request */
        protected $_request = null;
        /** @var Response */
        protected $_response = null;
        /** @var Globals */
        protected $_globals = null;
        /** @var Social */
        protected $_social = null;
        /** @var int */
        protected $_userId = null;
        /** @var string */
        protected $_viewPath = null;
        /** @var bool */
        protected $_withoutChecking = false;

        public function __construct() {
            //project settings
            $this->_globals = Globals::init();

            //load basic libs for response and secret checking
            $this->_response = new Response();

            //class with social functions
            $this->_social = new Social();

            //проверяем валидность запроса
            if(!$this->_withoutChecking) {
                $requestStatus = $this->getRequest()->getStatus();
                if($requestStatus != Response::CODE_OK) {
                    $this->_response->setCode($requestStatus)->send();
                }

                //проверяем авторизацию
                if(!$this->getUserId()) {
                    $this->_response->setCode(Response::CODE_NOT_AUTH)->send();
                }
            }

            //устанавливаем путь до вьюх
            $this->_viewPath = $this->_globals->getParam(Globals::SITE_PATH_PARAM) . 'core/views';
        }

        /**
         * Получить параметры запроса
         * @return Request
         */
        public function getRequest() {
            if ($this->_request == null) {
                $this->_request = new Request();
            }

            return $this->_request;
        }

        /**
         * Получить ID пользоватлея
         * @return int|null
         */
        public function getUserId() {
            if(!$this->_userId) {
                $this->_userId = $this->_social->getUserId();

                if(!$this->_userId && $this->_globals->isTestMode()) {
                    $this->_userId = $this->getRequest()->getParam(Request::USER_ID_PARAM, $this->_globals->getParam(Globals::TEST_MODE_USER_PARAM));
                }
            }

            return $this->_userId;
        }

        /**
         * Загружаем вьюху
         * @param string $viewPath
         * @param array $data
         * @param bool $flush
         * @return string
         */
        public function loadView($viewPath, $data = false, $flush = true) {
            $path = $this->_viewPath . DIRECTORY_SEPARATOR . $viewPath . '.php';
            if($path) {
                if(is_array($data))
                    extract($data);
                if($flush) {
                    include $path;
                } else {
                    ob_start();
                    include $path;
                    $res = ob_get_clean();
                    return $res;
                }
            }
            else {
                $this->_globals->getError()->setError("view '$viewPath.php' not found", 501);
            }
        }
    }
