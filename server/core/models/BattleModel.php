<?php
    class BattleModel extends RedisModel
    {
        /**
         * Создать самого себя
         *
         * @return BattleModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        public function testRedis() {
            $this->setValueByKey('test:key', 'Hello!');
            echo $this->getValueByKey('test:key');
        }
    }
