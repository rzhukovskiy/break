<?php
    /**
     * Контроллер отвечающий за отображение
     */
    class BattleController extends BaseController {
        /**
         * Основная страница
         */
        public function sendMessageAction() {
            $data = array(
                'type'      => $this->getRequest()->getParam('type', false),
                'message'   => $this->getRequest()->getParam('message', false)
            );
            BattleModel::getInstance()->sendMessage($this->getUserId(), $this->getRequest()->getParam('recipient', false), $data)->send();
        }
    }
