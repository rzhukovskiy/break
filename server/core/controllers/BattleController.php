<?php
    /**
     * Контроллер отвечающий за отображение
     */
    class BattleController extends BaseController {
        /**
         * Основная страница
         */
        public function testAction() {
            BattleModel::getInstance()->testRedis();
        }
    }
