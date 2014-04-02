<?php
    /**
     * Основной контроллер работающий с пользователем
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

            if($user->IsNotOk() || !$user->getData()) {
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
                'user_collections_list'     => UserCollectionsModel::getInstance()->getUserCollectionsListByUserId($this->getUserId())->getData(), //коллекции
                'user_mission_list'         => UserMissionModel::getInstance()->getUserMissionListByUserId($this->getUserId())->getData(), //миссии
                'user_award_list'           => UserAwardModel::getInstance()->getUserAwardListByUserId($this->getUserId())->getData(), //награды
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
         * Сохранение сообщение пользователя
         */
        public function saveLogAction() {
            $message = $this->getRequest()->getParam('message', false);
            $nickname = $this->getRequest()->getParam('nickname', 0);

            UserLogModel::getInstance()->saveUserLog($this->getUserId(), $nickname, $message)->send();
        }

        /**
         * Получение сообщений
         */
        public function getLogListAction() {
            UserLogModel::getInstance()->getUserLogList()->send();
        }

        /**
         * Сохранение события пользователя
         */
        public function saveEventAction() {
            $eventType = $this->getRequest()->getParam('game_id', false);
            $objectId = $this->getRequest()->getParam('object_id', 0);
            $userId = $this->getRequest()->getParam('user_id', $this->getUserId());
            $sender = $this->getRequest()->getParam('sender', 0);

            UserEventModel::getInstance()->saveUserEvent($userId, $eventType, $objectId, $sender)->send();
        }

        /**
         * Получение событий пользователя
         */
        public function getEventListAction() {
            $userId = $this->getRequest()->getParam('user_id', $this->getUserId());

            UserEventModel::getInstance()->getUserEventListByUserId($userId)->send();
        }

        /**
         * Получение событий пользователя
         */
        public function checkEventListAction() {
            UserEventModel::getInstance()->checkUserEventListByUserId($this->getUserId())->send();
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

            $res = UserMissionModel::getInstance()->saveMission($this->getUserId(), $missionId);

            if($res->IsNotOk()) {
                $res->send();
            }
            $res->setData(array(
                'user'                      => UserModel::getInstance()->getEntityByEntityId($this->getUserId())->getData(), //пользователь
                'user_mission_list'         => UserMissionModel::getInstance()->getUserMissionListByUserId($this->getUserId())->getData(), //миссии
            ))->send();
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

            if($res->IsNotOk()) {
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
         * Обмен баксов
         */
        public function buyChipsAction() {
            $bucks = $this->getRequest()->getParam('bucks', 0);

            UserModel::getInstance()->buyChips($this->getUserId(), $bucks);

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

            $res = UserModel::getInstance()->battleWin($this->getUserId(), $bet, $opponent);
            if($res->IsNotOk()) {
                $res->send();
            }

            $res->setData(array(
                'collections_id'            => UserCollectionsModel::getInstance()->buyUserCollections($this->getUserId())->getData(), //предмет коллекции
                'user_collections_list'     => UserCollectionsModel::getInstance()->getUserCollectionsListByUserId($this->getUserId())->getData(), //коллекции
                'user'                      => UserModel::getInstance()->getEntityByEntityId($this->getUserId())->getData(), //пользователь
            ))->send();
        }

        /**
         * Ничья в пвп
         */
        public function battleDrawAction() {
            $data = array(
                'draws' => 1
            );

            $res = UserModel::getInstance()->updateUserByUserId($this->getUserId(), $data);
            if($res->IsNotOk()) {
                $res->send();
            }

            $res->setData(array(
                'collections_id'            => UserCollectionsModel::getInstance()->buyUserCollections($this->getUserId())->getData(), //предмет коллекции
                'user_collections_list'     => UserCollectionsModel::getInstance()->getUserCollectionsListByUserId($this->getUserId())->getData(), //коллекции
                'user'                      => UserModel::getInstance()->getEntityByEntityId($this->getUserId())->getData(), //пользователь
            ))->send();
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
                'stamina' => -1 * $this->getRequest()->getParam('stamina', 1)
            );
            UserModel::getInstance()->updateUserByUserId($this->getUserId(), $data);

            UserModel::getInstance()->getEntityByEntityId($this->getUserId())->send();
        }

        /**
         * Уменьшение усталости
         */
        public function takeChipsAction() {
            $data = array(
                'chips' => -1 * $this->getRequest()->getParam('chips', 1)
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

            if($res->IsNotOk()) {
                $res->send();
            } else {
                $res->setData(array(
                    'user'                      => UserModel::getInstance()->getEntityByEntityId($this->getUserId())->getData(), //пользователь
                    'user_consumables_list'     => UserConsumablesModel::getInstance()->getUserConsumablesListByUserId($this->getUserId())->getData(), //предметы
                ))->send();
            }
        }

        /**
         * Передача предмета
         */
        public function giveConsumablesAction() {
            $recipientId    = $this->getRequest()->getParam('recipient_id', false);
            $consumablesId  = $this->getRequest()->getParam('consumables_id', false);

            $res = UserConsumablesModel::getInstance()->giveUserConsumables($this->getUserId(), $recipientId, $consumablesId);

            if($res->IsNotOk()) {
                $res->send();
            } else {
                $res->setData(array(
                    'user'                      => UserModel::getInstance()->getEntityByEntityId($this->getUserId())->getData(), //пользователь
                    'user_consumables_list'     => UserConsumablesModel::getInstance()->getUserConsumablesListByUserId($this->getUserId())->getData(), //предметы
                ))->send();
            }
        }

        /**
         * Передача предмета
         */
        public function giveCollectionsAction() {
            $recipientId    = $this->getRequest()->getParam('recipient_id', false);
            $collectionsId  = $this->getRequest()->getParam('collections_id', false);

            $res = UserCollectionsModel::getInstance()->giveUserCollections($this->getUserId(), $recipientId, $collectionsId);

            if($res->IsNotOk()) {
                $res->send();
            } else {
                $res->setData(array(
                    'user'                      => UserModel::getInstance()->getEntityByEntityId($this->getUserId())->getData(), //пользователь
                    'user_collections_list'     => UserCollectionsModel::getInstance()->getUserCollectionsListByUserId($this->getUserId())->getData(), //предметы
                ))->send();
            }
        }

        /**
         * Покупка предмета
         */
        public function applyConsumablesAction() {
            $res = UserConsumablesModel::getInstance()->applyUserConsumables($this->getUserId(), $this->getRequest()->getParam('consumables_id', false));

            if($res->IsNotOk()) {
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

            if($res->IsNotOk()) {
                $res->send();
            } else {
                $res->setData(array(
                    'user'                      => UserModel::getInstance()->getEntityByEntityId($this->getUserId())->getData(), //пользователь
                    'user_award_list'           => UserAwardModel::getInstance()->getUserAwardListByUserId($this->getUserId())->getData(), //миссии
                ))->send();
            }
        }

        /**
         * Учим движение
         */
        public function learnStepAction() {
            $res = UserStepModel::getInstance()->trainUserStep($this->getUserId(), $this->getRequest()->getParam('step_id', false), $this->getRequest()->getParam('energy_spent', 0));
            if($res->IsNotOk()) {
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
            if($res->IsNotOk()) {
                return $res;
            }
            return $userModel->restoreStamina($this->getUserId());
        }
    }
