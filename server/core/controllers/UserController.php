<?php
    /**
     * Основной контроллер работающий с пользователем
     * Пример вызова из адресной строки
     * http://bubble.battlekeys.com/server/index.php/user/progress?mission_id=loc1mis2&scores=100000
     * Данные можно передавать как в ПОСТ так и в ГЕТ массивах
     */
    class UserController extends BaseController {
        public function __construct($action = '') {
            /** @var bool */
            if($action == 'getAction') {
                $this->_withoutChecking = true;
            }
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
            $userData = $user->getData();
            if($userData['banned']) {
                die;
            }
            $response->setData(array(
                'user'                      => $userData, //пользователь
                'user_settings'             => UserSettingsModel::getInstance()->getEntityByEntityId($this->getUserId())->getData(), //настройки
                'user_news_list'            => UserNewsModel::getInstance()->getUserNewsListByUserId($this->getUserId())->getData(), //новинки
                'user_tutorial_list'        => UserTutorialModel::getInstance()->getUserTutorialListByUserId($this->getUserId())->getData(), //туториал
                'user_mission_list'         => UserMissionModel::getInstance()->getUserMissionListByUserId($this->getUserId())->getData(), //туториал
                'user_award_list'           => UserAwardModel::getInstance()->getUserAwardListByUserId($this->getUserId())->getData(), //туториал
                'user_item_list'            => UserItemModel::getInstance()->getUserItemListByUserId($this->getUserId())->getData(), //предметы
                'user_consumables_list'     => UserConsumablesModel::getInstance()->getUserConsumablesListByUserId($this->getUserId())->getData(), //предметы
                'user_scores_list'          => UserScoresModel::getInstance()->getUserScoresListByUserId($this->getUserId())->getData(), //очки
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
            $ids = $this->getRequest()->getParam('uids', false);
            $ids = array_map('intval', explode(',', $ids));

            $userList = array();
            foreach($ids as $id) {
                $userList[$id] =  array(
                    'user'                      => UserModel::getInstance()->getEntityByEntityId($id)->getData(), //пользователь
                    'user_scores_list'          => UserScoresModel::getInstance()->getUserScoresListByUserId($id)->getData(), //очки
                    'user_item_list'            => UserItemModel::getInstance()->getUserItemListByUserId($id)->getData(), //предметы
                    'user_slot_list'            => UserSlotModel::getInstance()->getUserSlotListByUserId($id)->getData(), //слоты
                );
            }

            $response = new Response();
            $response->setData($userList)->send();
        }

        /**
         * Сохранение очков пользователя
         */
        public function saveUserScoresAction() {
            $gameId = $this->getRequest()->getParam('game_id', false);
            $scores = $this->getRequest()->getParam('scores', 0);

            UserScoresModel::getInstance()->saveUserScores($this->getUserId(), $gameId, $scores)->send();
        }

        /**
         * Сохранение тутора пользователя
         */
        public function saveTutorialStepAction() {
            $tutorialId = $this->getRequest()->getParam('tutorial_id', false);

            UserTutorialModel::getInstance()->saveTutorial($this->getUserId(), $tutorialId)->send();
        }

        /**
         * Сохранение тутора пользователя
         */
        public function saveMissionAction() {
            $missionId = $this->getRequest()->getParam('mission_id', false);

            UserMissionModel::getInstance()->saveMission($this->getUserId(), $missionId)->send();
        }

        /**
         * Изменение внешности и имени пользователя
         */
        public function updateUserAppearanceAction() {
            $faceId = $this->getRequest()->getParam('face_id', 1);
            $hairId = $this->getRequest()->getParam('hair_id', 1);
            $nickname = $this->getRequest()->getParam('nickname', $this->getUserId());

            $res = UserModel::getInstance()->updateUserAppearanceByUserId($this->getUserId(), array(
                'face_id' => $faceId,
                'hair_id' => $hairId,
                'nickname' => $nickname
            ));

            if($res->isError()) {
                $res->send();
            } else {
                UserModel::getInstance()->getEntityByEntityId($this->getUserId())->send();
            }
        }

        /**
         * Обмен баксов
         */
        public function sellBucksAction() {
            $bucks = $this->getRequest()->getParam('bucks', 0);

            UserModel::getInstance()->sellBucks($this->getUserId(), $bucks);

            UserModel::getInstance()->getEntityByEntityId($this->getUserId())->send();
        }

        /**
         * Сохранение очков пользователя
         */
        public function getTopUsersAction() {
            $amount = $this->getRequest()->getParam('amount', 10);
            $days = $this->getRequest()->getParam('days', 1);

            $ids = UserScoresModel::getInstance()->getTopUserList($amount, $days)->getData();

            foreach($ids as &$id) {
                $id['user']                      = UserModel::getInstance()->getEntityByEntityId($id['user_id'])->getData();
                $id['user_scores_list']          = UserScoresModel::getInstance()->getUserScoresListByUserId($id['user_id'])->getData();
                $id['user_item_list']            = UserItemModel::getInstance()->getUserItemListByUserId($id['user_id'])->getData();
                $id['user_slot_list']            = UserSlotModel::getInstance()->getUserSlotListByUserId($id['user_id'])->getData();
            }

            $response = new Response();
            $response->setData($ids)->send();
        }

        /**
         * Добавление нового пользователя
         */
        public function addAction() {
            $faceId = $this->getRequest()->getParam('face_id', 1);
            $hairId = $this->getRequest()->getParam('hair_id', 1);
            $nickname = $this->getRequest()->getParam('nickname', $this->getUserId());

            UserModel::getInstance()->addUserByUserId($this->getUserId(), $faceId, $hairId, $nickname)->send();
        }

        /**
         * Добавление нового пользователя
         */
        public function saveSettingsAction() {
            $music = $this->getRequest()->getParam('music', 1);
            $sfx = $this->getRequest()->getParam('sfx', 1);
            $lang = $this->getRequest()->getParam('lang', 1);
            $bet = $this->getRequest()->getParam('bet', 50);
            $moves = $this->getRequest()->getParam('turns', 1);

            UserSettingsModel::getInstance()->updateSettingsByUserId($this->getUserId(), array(
                'music' => $music,
                'sfx' => $sfx,
                'lang' => $lang,
                'bet' => $bet,
                'turns' => $moves
            ))->send();
        }

        /**
         * Победа в пвп
         */
        public function battleWinAction() {
            $bet = $this->getRequest()->getParam('bet', 0);
            $opponent = $this->getRequest()->getParam('opponent', false);

            UserModel::getInstance()->battleWin($this->getUserId(), $bet, $opponent)->send();
        }

        /**
         * Проигрыш в пвп
         */
        public function battleLoseAction() {
            $bet = $this->getRequest()->getParam('bet', 0);
            $opponent = $this->getRequest()->getParam('opponent', false);

            UserModel::getInstance()->battleLose($this->getUserId(), $bet, $opponent)->send();
        }

        /**
         * Уменьшение усталости
         */
        public function takeStaminaAction() {
            $data = array(
                'stamina' => -1 * $this->getRequest()->getParam('stamina', 0)
            );
            UserModel::getInstance()->updateUserByUserId($this->getUserId(), $data);

            UserModel::getInstance()->getEntityByEntityId($this->getUserId())->send();
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
            UserModel::getInstance()->giveDailyAward($this->getUserId());

            UserModel::getInstance()->getEntityByEntityId($this->getUserId())->send();
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
         * Покупка предмета
         */
        public function buyConsumablesAction() {
            $res = UserConsumablesModel::getInstance()->buyUserConsumables($this->getUserId(), $this->getRequest()->getParam('consumables_id', false), $this->getRequest()->getParam('count', 1));

            if($res->isError()) {
                $res->send();
            } else {
                $res->setData(array(
                    'user'                      => UserModel::getInstance()->getEntityByEntityId($this->getUserId())->getData(), //пользователь
                    'user_consumables_list'     => UserConsumablesModel::getInstance()->getUserConsumablesListByUserId($this->getUserId())->getData(), //предметы
                ))->send();
            }
        }

        /**
         * Покупка предмета
         */
        public function applyConsumablesAction() {
            $res = UserConsumablesModel::getInstance()->applyUserConsumables($this->getUserId(), $this->getRequest()->getParam('consumables_id', false));

            if($res->isError()) {
                $res->send();
            } else {
                $res->setData(array(
                    'user'                      => UserModel::getInstance()->getEntityByEntityId($this->getUserId())->getData(), //пользователь
                    'user_consumables_list'     => UserConsumablesModel::getInstance()->getUserConsumablesListByUserId($this->getUserId())->getData(), //предметы
                ))->send();
            }
        }

        /**
         * Продажа предмета
         */
        public function sellItemAction() {
            UserItemModel::getInstance()->sellUserItem($this->getUserId(), $this->getRequest()->getParam('user_item_id', false))->send();
        }

        /**
         * Добавление новинки
         */
        public function addNewsAction() {
            UserNewsModel::getInstance()->addUserNews($this->getUserId(), $this->getRequest()->getParam('item_id', false))->send();
        }

        /**
         * Удаление новинок
         */
        public function removeNewsAction() {
            UserNewsModel::getInstance()->removeUserNews($this->getUserId(), $this->getRequest()->getParam('ids', false))->send();
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
         * Даем награду.
         */
        public function giveAwardAction() {
            $awardId = $this->getRequest()->getParam('award_id', 'wrong_award');

            $res = UserAwardModel::getInstance()->giveAward($this->getUserId(), $awardId);

            if($res->isError()) {
                $res->send();
            } else {
                UserModel::getInstance()->getEntityByEntityId($this->getUserId())->send();
            }
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
