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
                'user_settings'             => UserSettingsModel::getInstance()->getEntityByEntityId($this->getUserId())->getData(), //настройки
                'user_item_list'            => UserItemModel::getInstance()->getUserItemListByUserId($this->getUserId())->getData(), //предметы
                'user_slot_list'            => UserSlotModel::getInstance()->getUserSlotListByUserId($this->getUserId())->getData(), //слоты
                'user_step_list'            => UserStepModel::getInstance()->getUserStepListByUserId($this->getUserId())->getData(), //движения
                'user_request_from_list'    => RequestModel::getInstance()->getRequestListByUserFromId($this->getUserId())->getData(), //запросы
                'user_request_to_list'      => RequestModel::getInstance()->getRequestListByUserToId($this->getUserId())->getData(), //запросы
            ))->send();
        }

        /**
         * Запрос всех данных о списке пользователей
         */
        public function getListAction() {
            $userModel = UserModel::getInstance();
            $userModel->getUserListByIds($this->getRequest()->getParam('uids', false))->send();
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
         * Добавление нового пользователя
         */
        public function saveSettingsAction() {
            $music = $this->getRequest()->getParam('music', 1);
            $sfx = $this->getRequest()->getParam('sfx', 1);
            $lang = $this->getRequest()->getParam('lang', 1);

            UserSettingsModel::getInstance()->updateSettingsByUserId($this->getUserId(), array(
                'music' => $music,
                'sfx' => $sfx,
                'lang' => $lang
            ))->send();
        }

        /**
         * Удаление пользователя
         */
        public function deleteAction() {
            UserModel::getInstance()->deleteUserByUserId($this->getUserId())->send();
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
            UserItemModel::getInstance()->buyUserItem($this->getUserId(), $this->getRequest()->getParam('item_id', false), $this->getRequest()->getParam('color', 'no_color'))->send();
        }

        /**
         * Продажа предмета
         */
        public function sellItemAction() {
            UserItemModel::getInstance()->sellUserItem($this->getUserId(), $this->getRequest()->getParam('user_item_id', false))->send();
        }

        /**
         * Экипировка предмета
         */
        public function equipSlotAction() {
            UserSlotModel::getInstance()->equipUserSlot($this->getUserId(),
                $this->getRequest()->getParam('slot_id', false),
                $this->getRequest()->getParam('user_item_id', false)
            )->send();
        }

        /**
         * Даем награду. Не забыть убрать из продакшена
         */
        public function awardAction() {
            $data = array(
                'coins'         => $this->getRequest()->getParam('coins') ? $this->getRequest()->getParam('coins') : 0,
                'chips'        => $this->getRequest()->getParam('chips') ? $this->getRequest()->getParam('chips') : 0,
                'energy'       => $this->getRequest()->getParam('energy') ? $this->getRequest()->getParam('energy') : 0,
                'energy_max'   => $this->getRequest()->getParam('energy_max') ? $this->getRequest()->getParam('energy_max') : 0,
                'stamina'      => $this->getRequest()->getParam('stamina') ? $this->getRequest()->getParam('stamina') : 0,
                'stamina_max'  => $this->getRequest()->getParam('stamina_max') ? $this->getRequest()->getParam('stamina_max') : 0,
                'energy_time'  => $this->getRequest()->getParam('energy_time') ? $this->getRequest()->getParam('energy_time') : 0,
                'stamina_time' => $this->getRequest()->getParam('stamina_time') ? $this->getRequest()->getParam('stamina_time') : 0,
                'energy_spent' => $this->getRequest()->getParam('energy_spent') ? $this->getRequest()->getParam('energy_spent') : 0,
                'wins'         => $this->getRequest()->getParam('wins') ? $this->getRequest()->getParam('wins') : 0,
                'battles'      => $this->getRequest()->getParam('battles') ? $this->getRequest()->getParam('battles') : 0,
                'level'        => $this->getRequest()->getParam('level') ? $this->getRequest()->getParam('level') : 0,
            );
            UserModel::getInstance()->updateUserByUserId($this->getUserId(), $data)->send();
        }

        /**
         * Учим движение
         */
        public function learnStepAction() {
            $res = UserStepModel::getInstance()->trainUserStep($this->getUserId(), $this->getRequest()->getParam('step_id', false), $this->getRequest()->getParam('energy_spent', 0));
            if($res->isError()) {
                $res->send();
            } else {
                $this->getAction();
            }
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