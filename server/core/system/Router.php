<?php
    /**
     * Роутер
     */
    final class Router {
        const NOT_FOUND = "url not found";
        /** @var Globals */
        protected $_globals = null;

        public function __construct() {
            $this->_globals = Globals::init();
        }

        /**
         * Разбираем путь, чтобы получить имя контроллера и метода
         * @param string $path
         */
        public function pathParsing($path) {
            //default controller loading
            if ($path === '') {
                $controllerName = 'IndexController';
            } else {
                $path = explode('/', str_replace('%20', ' ', trim($path, "/\\")));
                $controllerName = ucfirst($path[0]) . 'Controller';
            }

            if(isset($path[1])) {
                $action = $path[1] . 'Action';
            } else {
                $action = 'indexAction';
            }
            //path parsing and loading controller

            if(is_file('core/controllers/' . $controllerName . '.php')) {
                $this->loadController($controllerName, $action);
            } else {
                $this->_globals->getError()->setError(self::NOT_FOUND, 404);
            }
        }

        /**
         * Создаем эксземпляр соответствующего контроллера и запускаем метод
         * @param string $controllerName
         * @param string $action
         */
        private function loadController($controllerName, $action) {
            try {
                $controller = new $controllerName($action);
                if(method_exists($controllerName, $action)) {
                    call_user_func_array(array($controller, $action), array());
                } else if(method_exists($controllerName, 'indexAction')) {
                    call_user_func_array(array($controller, 'indexAction'), array());
                } else {
                    $this->_globals->getError()->setError(self::NOT_FOUND, 404);
                }
            } catch(Exception $e) {
                $this->_globals->getError()->setError($e->getMessage(), 500);
            }
        }
    }
