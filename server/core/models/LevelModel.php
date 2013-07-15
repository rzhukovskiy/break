<?php
    /**
     * Работа с таблицей amulet - амулеты в игре
     */
    class LevelModel extends BaseModel {
        protected $_table = 'level';

        /**
         * Создать самого себя
         *
         * @return LevelModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }
    }
