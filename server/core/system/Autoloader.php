<?php
    /**
     * Автолоадер
     */
    class Autoloader {
        static protected $_paths = array();

        //регистрация путей
        static public function registerPath($path) {
            if(!in_array($path , self::$_paths))
                self::$_paths[] = $path;
        }

        //загрузка классов
        public static function load($class) {
            $file = $class . '.php';

            foreach(self::$_paths as $path){
                if(file_exists($path . DIRECTORY_SEPARATOR . $file)){
                    include $path . DIRECTORY_SEPARATOR . $file;
                    return true;
                }
            }
            return false;
        }
    }