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
                'message'   => json_decode(utf8_encode($this->getRequest()->getParam('message', false)), true)
            );
            BattleModel::getInstance()->sendBattleMessage($this->getUserId(), $this->getRequest()->getParam('recipient', false), $data)->send();
        }
    }
