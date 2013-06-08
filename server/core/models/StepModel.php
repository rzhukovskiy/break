<?php
    /**
     * Работа с таблицей amulet - амулеты в игре
     */
    class StepModel extends BaseModel {
        protected $_table = 'step';

        /**
         * Создать самого себя
         *
         * @return StepModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }
    }
