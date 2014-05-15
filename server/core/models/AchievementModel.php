<?php
    /**
     * Работа с таблицей achievement - ачивки в игре
     */
    class AchievementModel extends BaseModel {
        protected $_table = 'achievement';

        /**
         * Создать самого себя
         *
         * @return AchievementModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }
    }
