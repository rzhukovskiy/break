<?php
    /**
     * Работа с таблицей amulet - амулеты в игре
     */
    class ItemModel extends BaseModel {
        protected $_table = 'item';

        /**
         * Создать самого себя
         *
         * @return ItemModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }
    }
