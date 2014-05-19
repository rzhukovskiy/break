<?php
    /**
     * работа с user_move таблицей. движения пользователей
     */
    class UserAchievementModel extends BaseModel {
        protected $_table = 'user_achievement';

        /**
         * Создать самого себя
         *
         * @return UserAchievementModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Список движений у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserAchievementListByUserId($userId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
               'SELECT
                    *
                FROM
                    ' . $this->_table . '
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
         * Получить конкретное движение у конкретного пользователя
         * @param int $userId
         * @param string $achievementId
         * @return Response
         */
        public function getUserAchievementByUserIdAndAchievementId($userId, $achievementId) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'SELECT
                    *
                FROM
                    ' . $this->_table . '
                WHERE
                    user_id = :user_id AND
                    achievement_id = :achievement_id
                LIMIT 1';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId,
                ':achievement_id' => $achievementId,
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
         * Инкрементим прогресс ачивки
         * @param int $userId
         * @param string $achievementId
         * @return Response
         */
        public function incrementUserAchievement($userId, $achievementId) {
            /** @var $dataDb PDO */
            $response = new Response();

            $achievement = AchievementModel::getInstance()->getEntityByEntityId($achievementId);
            if($achievement->IsNotOk()) {
                return $achievement;
            }
            $achievement = $achievement->getData();

            $userAchievement = $this->getUserAchievementByUserIdAndAchievementId($userId, $achievementId);
            if($userAchievement->IsNotOk()) {
                return $userAchievement;
            }
            $userAchievement = $userAchievement->getData();

            $phase = $userAchievement['phase'];

            if(!isset($achievement['phase' . $phase]) || !$achievement['phase' . $phase]) {
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('Wrong phase');
            }

            if(($userAchievement['points'] + 1) == $achievement['phase' . $phase]) {
                $phase++;
                $awardResult = UserModel::getInstance()->giveAward($userId, $achievement['award' . $phase . '_id']);

                if($awardResult->IsNotOk()) {
                    return $awardResult;
                }
            }

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, achievement_id, points, phase, create_date)
                VALUES
                    (:user_id, :achievement_id, :points, 1, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    points = points + 1,
                    phase = :phase';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'          => $userId,
                ':achievement_id'   => $achievementId,
                ':phase'            => $phase
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }

        /**
         * Инкрементим прогресс ачивки
         * @param int $userId
         * @param string $achievementId
         * @param int $value
         * @return Response
         */
        public function setUserAchievement($userId, $achievementId, $value = 1) {
            /** @var $dataDb PDO */
            $response = new Response();

            $achievement = AchievementModel::getInstance()->getEntityByEntityId($achievementId);
            if($achievement->IsNotOk()) {
                return $achievement;
            }
            $achievement = $achievement->getData();

            $userAchievement = $this->getUserAchievementByUserIdAndAchievementId($userId, $achievementId);
            if($userAchievement->IsNotOk()) {
                return $userAchievement;
            }
            $userAchievement = $userAchievement->getData();

            $phase = $userAchievement['phase'];

            if(!isset($achievement['phase' . $phase]) || !$achievement['phase' . $phase]) {
                return $response->setCode(Response::CODE_WRONG_DATA)->setError('Achievement completed already');
            }

            if($value >= $achievement['phase' . $phase]) {
                $phase++;
                $awardResult = UserModel::getInstance()->giveAward($userId, $achievement['award' . $phase . '_id']);

                if($awardResult->IsNotOk()) {
                    return $awardResult;
                }
            }

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, achievement_id, points, phase, create_date)
                VALUES
                    (:user_id, :achievement_id, :points, :value, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    points = value,
                    phase = :phase';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'          => $userId,
                ':achievement_id'   => $achievementId,
                ':phase'            => $phase,
                ':value'            => $value
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }
    }
