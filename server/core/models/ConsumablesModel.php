<?php
    /**
     * Работа с таблицей amulet - амулеты в игре
     */
    class ConsumablesModel extends BaseModel {
        protected $_table = 'consumables';

        /**
         * Создать самого себя
         *
         * @return ConsumablesModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }
    }
