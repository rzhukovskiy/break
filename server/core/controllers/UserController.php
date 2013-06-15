<?php
    /**
     * Основной контроллер работающий с пользователем
     * Пример вызова из адресной строки
     * http://bubble.battlekeys.com/server/index.php/user/progress?mission_id=loc1mis2&scores=100000
     * Данные можно передавать как в ПОСТ так и в ГЕТ массивах
     */
    class UserController extends BaseController {
        public function __construct() {
            parent::__construct();

            //проверяем таймеры пользователя (восстанавливаем энергию и т.п.)
            $this->_checkTimers();
        }

        /**
         * Запрос всех данных о пользователе. Вызывается при запуске игры
         */
        public function getAction() {
            $userModel = UserModel::getInstance();
            $user = $userModel->getEntityByEntityId($this->getUserId());

            if($user->isError() || !$user->getData()) {
                $user->send();
            }

            $response = new Response();
            $response->setData(array(
                'user'                      => $user->getData(), //пользователь
                'user_item_list'            => UserItemModel::getInstance()->getUserItemListByUserId($this->getUserId())->getData(), //предметы
                'user_step_list'            => UserStepModel::getInstance()->getUserStepListByUserId($this->getUserId())->getData(), //движения
                'user_request_from_list'    => RequestModel::getInstance()->getRequestListByUserFromId($this->getUserId())->getData(), //запросы
                'user_request_to_list'      => RequestModel::getInstance()->getRequestListByUserToId($this->getUserId())->getData(), //запросы
            ))->send();
        }

        /**
         * Добавление нового пользователя
         */
        public function addAction() {
            $faceId = $this->getRequest()->getParam('face_id', 1);
            $hairId = $this->getRequest()->getParam('hair_id', 1);

            UserModel::getInstance()->addUserByUserId($this->getUserId(), $faceId, $hairId)->send();
        }

        /**
         * Даем ежедневную награду
         */
        public function dailyAwardAction() {
            UserModel::getInstance()->giveDailyAward($this->getUserId())->send();
        }

        /**
         * Принудительная проверка таймеров пользователей. Возвращает текущее состояние пользователя
         */
        public function updateAction() {
            $userModel = UserModel::getInstance();

            $this->_checkTimers();
            $userModel->getEntityByEntityId($this->getUserId())->send();
        }

        /**
         * Покупка предмета
         */
        public function buyItemAction() {
            UserItemModel::getInstance()->buyUserItem($this->getUserId(), $this->getRequest()->getParam('item_id', false))->send();
        }

        /**
         * Учим движение
         */
        public function learnStepAction() {
            UserStepModel::getInstance()->trainUserStep($this->getUserId(), $this->getRequest()->getParam('step_id', false))->send();
        }

        /**
         * Проверка таймеров пользователя
         * @return Response
         */
        private function _checkTimers() {
            $userModel = UserModel::getInstance();

            //Восстанавливаем энергию, если прошло более заданного времени
            $res = $userModel->restoreEnergy($this->getUserId());
            if($res->isError()) {
                return $res;
            }
            return $userModel->restoreStamina($this->getUserId());
        }
    }