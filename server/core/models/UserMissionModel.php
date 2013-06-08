<?php
    /**
     * Работа с таблицей user_mission. Прогресс полдьзователя в прохождении
     */
    class UserMissionModel extends BaseModel {
        /**
         * Создать самого себя
         *
         * @return UserMissionModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список миссий, пройденных пользователем
         * @param int $userId
         * @return Response
         */
        public function getUserMissionListByUserId($userId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
               'SELECT
                    *
                FROM
                    user_mission
                WHERE
                    user_id = :user_id';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($query->fetchAll(PDO::FETCH_ASSOC));
            }
            return $response;
        }

        /**
         * Список миссий, пройденных пользователем
         * @param string $userList
         * @param string $missionId
         * @return Response
         */
        public function getUserScoresListByUserListAndMissionId($userList, $missionId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            if(count($userList) == 0) {
                return $response;
            }

            $sql =
                'SELECT
                    *
                FROM
                    user_mission
                WHERE
                    user_id IN (' . implode(',', $userList) . ') AND
                    mission_id = :mission_id';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':mission_id' => $missionId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($query->fetchAll(PDO::FETCH_ASSOC));
            }
            return $response;
        }

        /**
         * Количество звезд, полученных пользователем
         * @param int $userId
         * @return Response
         */
        public function getUserMissionStars($userId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'SELECT
                    SUM(stars) as total_stars
                FROM
                    user_mission
                WHERE
                    user_id = :user_id';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($query->fetch(PDO::FETCH_ASSOC));
            }
            return $response;
        }

        /**
         * Получить конкретную миссию конкретного пользователя
         * @param int $userId
         * @param string $missionId
         * @return Response
         */
        public function getUserMissionByUserIdAndMissionId($userId, $missionId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
               'SELECT
                    *
                FROM
                    user_mission
                WHERE
                    user_id = :user_id AND
                    mission_id = :mission_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':mission_id' => $missionId,
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($query->fetch(PDO::FETCH_ASSOC));
            }
            return $response;
        }

        /**
         * Обновляем прогресс пользователя в миссии
         * @param int $userId
         * @param string $missionId
         * @param int $scores
         * @return Response
         */
        public function updateUserMissionByUserIdAndMissionId($userId, $missionId, $scores) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            //забираем данные о миссии из игровой базы
            $missionModel = MissionModel::getInstance();
            $mission = $missionModel->getMissionByMissionId($missionId);
            if($mission->isError()) {
                return $mission;
            } else {
                $mission = $mission->getData();
            }

            //проверяем, прошел ли игрок предыдущую миссию
            if($mission['parent_id']) {
                $previousMission = $this->getUserMissionByUserIdAndMissionId($userId, $mission['parent_id']);
                if($previousMission->isError()) {
                    return $previousMission;
                } else {
                    $previousMission = $previousMission->getData();
                    //если не прошел - давай до свидания
                    if($previousMission['stars'] < 1) {
                        $response->setCode(Response::CODE_WRONG_DATA)->setError('Previous mission not completed');
                        return $response;
                    }
                }
            }

            //проверяем предыдущие рекорды в этой же миссии
            $awardId = 'award5'; //награда по умолчанию - за просто повторное прохождение
            $previousTry = $this->getUserMissionByUserIdAndMissionId($userId, $mission['id']);
            if($previousTry->isError()) {
                return $previousTry;
            }
            $previousTry = $previousTry->getData();

            //смотрим сколько звезд дать юзверю за миссию
            $stars = 0;
            if ($mission['star1'] <= $scores) $stars = 1;
            if ($mission['star2'] <= $scores) $stars = 2;
            if ($mission['star3'] <= $scores) $stars = 3;

            //устанавливаем какую награду дать пользователю, в зависимости от звезд и предыдущих результатов
            if ((isset($previousTry['stars']) && $stars > $previousTry['stars']) || !isset($previousTry['stars'])) { //первое получение звезды
                switch ($stars) {
                    case 1:
                        $awardId = 'award1';
                        break;
                    case 2:
                        $awardId = 'award2';
                        break;
                    case 3:
                        $awardId = 'award3';
                        break;
                }
            } else if (isset($previousTry['scores']) && $scores > $previousTry['scores']) { //повторное получение звезды, но новый рекорд по очкам
                $awardId = 'award4';
            }

            //если игрок получил звезду, возвращаем отнятую ранее энергию
            if($stars) {
                $response = UserModel::getInstance()->giveEnergy($userId);

                if($response->isError()) {
                    return $response;
                }
            } else {
                $response = UserModel::getInstance()->updateEnergyDateByUserId($userId);

                if($response->isError()) {
                    return $response;
                }
            }

            //даем награду
            $awardResponse = UserModel::getInstance()->updateUserByUserId($userId, array('coins' => $mission[$awardId]));
            if($awardResponse->isError()) {
                return $awardResponse;
            }

            //вычисляем, какой предмет полагается игроку (пока рандом из заданной для данной локации коллекции)
            $collection = CollectionModel::getInstance()->getCollectionByLocationId($mission['location_id']);
            if($collection->isError()) {
                return $collection;
            }
            $collection = $collection->getData();
            $rand = rand(1, 6);
            if($rand != 6 && isset($collection['item'.$rand])) {
                $response = UserItemModel::getInstance()->addUserItem($userId, $collection['item'.$rand]);
            }

            //смотрим не должны ли мы дать пользователю скролл
            $scrollId = '';
            $totalStars = UserMissionModel::getInstance()->getUserMissionStars($userId);
            if($totalStars->isError()) {
                return $stars;
            }
            $totalStars = $totalStars->getData();

            $scroll = ScrollModel::getInstance()->getScrollByStarsAmount($totalStars['total_stars']);
            if($scroll->isError()) {
                return $scroll;
            }
            $scroll = $scroll->getData();

            if($scroll) {
                $userScroll = UserScrollModel::getInstance()->getUserScrollByUserIdAndScrollId($userId, $scroll['id']);
                if(!$userScroll->getData()) {
                    $scrollId = $scroll['id'];
                    $response = UserScrollModel::getInstance()->addUserScroll($userId, $scroll['id']);
                    if($response->isError()) {
                        return $response;
                    }
                }
            }

            //сохраняем. очки и звезды перезаписываем только если превзойден предыдущий результат
            $sql =
               'INSERT INTO
                    user_mission
                    (user_id, mission_id, scores, stars, modify_date, tries)
                VALUES
                    (:user_id, :mission_id, :scores, :stars, CURRENT_TIMESTAMP, 1)
                ON DUPLICATE KEY UPDATE
                    scores = GREATEST(scores, :scores),
                    stars = GREATEST(stars, :stars),
                    modify_date = CURRENT_TIMESTAMP,
                    tries = tries + 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':mission_id'   => $missionId,
                ':scores'       => $scores,
                ':stars'        => $stars
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }
            //возвращаем полученные звезды и выданный предмет
            $response->setData(array(
                'stars'     => $stars,
                'item_id'   => isset($collection['item'.$rand]) ? $collection['item'.$rand] : '',
                'award'     => array('coins' => $mission[$awardId]),
                'scroll_id' => $scrollId,
            ));

            return $response;
        }
    }
