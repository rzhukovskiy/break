<?php
    /**
     * Контроллер отвечающий за отображение
     */
    class BattleController extends BaseController {
        /**
         * Основная страница
         */
        public function testAction() {
            BattleModel::getInstance()->sendMessage($this->getUserId(), $this->getRequest()->getParam('recipient', false), json_decode($this->getRequest()->getParam('message', false), true))->send();
        }
    }
