<?php
    /**
     * Работа с тблицей пользователей
     */
    class UserModel extends BaseModel {
        protected $_table = 'user';

        /**
         * Создать самого себя
         *
         * @return UserModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Проверить занятость nickname
         * @param string $nickname
         * @return Response
         */
        public function checkNickname($nickname) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT * FROM ' . $this->_table . ' WHERE nickname = :nickname';
            $query = $gameDb->prepare($sql);
            $query->execute(array(':nickname' => $nickname));

            $err = $query->errorInfo();
            if($err[1] != null){
                return false;
            } else {
                return $query->rowCount() > 0;
            }
        }

        /**
         * Получить список пользователей по ID
         * @param string $ids
         * @return Response
         */
        public function getUserListByIds($ids) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $ids = implode(',', array_map('intval', explode(',', $ids)));
            $sql = 'SELECT * FROM ' . $this->_table . ' WHERE id IN (' . $ids . ')';
            $query = $gameDb->prepare($sql);
            $query->execute();

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($query->fetchAll(PDO::FETCH_ASSOC));
            }
            return $response;
        }

        private function _raiseUserLevel($userId, $energySpent, $wins) {
            $settings = $this->getSettingList();
            $response = new Response();
            $user = $this->getEntityByEntityId($userId);
            if($user->isError()) {
                return $user;
            }
            $user = $user->getData();

            $level = LevelModel::getInstance()->getEntityByEntityId($user['level'] + 1);
            if($level->isError()) {
                return $level;
            }
            $level = $level->getData();

            if(($user['energy_spent'] + $energySpent >= $level['energy']) && ($user['wins'] + $wins >= $level['wins'])) {
                $awardResult = $this->giveAward($userId, $level['award']);
                if($awardResult->isError()) {
                    return $awardResult;
                }
                $data = array(
                    'energy_spent' => -1,
                    'stamina_max'  => $level['stamina_max'] - $user['stamina_max'],
                    'row_wins'     => $wins,
                    'level'        => 1
                );

                $this->_social->setLevel($user['level'] + 1);
            } else {
                $data = array(
                    'energy_spent' => $user['energy_spent'] + $energySpent,
                    'stamina_max'  => 0,
                    'row_wins'     => $wins,
                    'level'        => 0
                );
            }

            if(($user['row_wins'] + $wins) >= $settings['num_wins_in_row']) {
                $this->giveAward($userId, $settings['award_wins_in_row']);
                $data['row_wins'] = -1;
            }

            $response->setData($data);
            return $response;
        }

        /**
         * Добавляем пользователю награду
         * @param int $userId
         * @param string $awardId
         * @return Response
         */
        public function giveAward($userId, $awardId) {
            /** @var $db PDO */
            $db = $this->getDataBase();
            $response = new Response();

            $award = AwardModel::getInstance()->getAwardByAwardId($awardId);
            if($award->isError()) {
                return $award;
            }
            $award = $award->getData();

            if(empty($award)) {
                return $response;
            }

            $response = $this->updateUserByUserId($userId, $award);

            if($response->isError()) {
                return $response;
            }

            if(isset($award['item_id']) && $award['item_id']) {
                $response = UserItemModel::getInstance()->addUserItem($userId, $award['item_id']);

                if($response->isError()) {
                    return $response;
                }
            }

            $response->setData($award);
            return $response;
        }

        /**
         * Обновляем табличку user
         * @param int $userId
         * @param array $data
         * @return Response
         */
        public function updateUserByUserId($userId, $data) {
            /** @var $db PDO */
            $db = $this->getDataBase();
            $response = new Response();

            $energySpent = isset($data['energy_spent']) ? $data['energy_spent'] : 0;
            $wins        = isset($data['wins']) ? $data['wins'] : 0;
            $rowWins =   isset($data['row_wins']) ? $data['row_wins'] : 0;
            $staminaMax  = 0;
            $level       = 0;
            if($wins || $energySpent) {
                $newData = $this->_raiseUserLevel($userId, $energySpent, $wins);
                if($newData->isError()) {
                    return $newData;
                }
                $newData = $newData->getData();

                $energySpent = $newData['energy_spent'];
                $staminaMax  = $newData['stamina_max'];
                $level       = $newData['level'];
                $rowWins = $newData['row_wins'];
            }

            $updateData = array(
                ':user_id'      => $userId,
                ':coins'        => isset($data['coins']) ? $data['coins'] : 0,
                ':bucks'        => isset($data['bucks']) ? $data['bucks'] : 0,
                ':chips'        => isset($data['chips']) ? $data['chips'] : 0,
                ':energy'       => isset($data['energy']) ? $data['energy'] : 0,
                ':energy_max'   => isset($data['energy_max']) ? $data['energy_max'] : 0,
                ':stamina'      => isset($data['stamina']) ? $data['stamina'] : 0,
                ':stamina_max'  => $staminaMax,
                ':energy_time'  => isset($data['energy_time']) ? $data['energy_time'] : 0,
                ':stamina_time' => isset($data['stamina_time']) ? $data['stamina_time'] : 0,
                ':wins'         => $wins,
                ':battles'      => isset($data['battles']) ? $data['battles'] : 0,
                ':level'        => $level
            );

            $sql =
                'UPDATE
                  user
                SET
                  coins        = coins + :coins,
                  bucks        = bucks + :bucks,
                  chips        = chips + :chips,
                  energy       = LEAST(energy + :energy, energy_max),
                  energy_max   = energy_max + :energy_max,
                  stamina      = LEAST(stamina + :stamina, stamina_max),
                  stamina_max  = stamina_max + :stamina_max,
                  energy_time  = energy_time + (:energy_time * energy_time) / 100,
                  stamina_time = stamina_time + (:stamina_time * stamina_time) / 100, ';
            if($energySpent > 0) {
                $sql .= 'energy_spent = :energy_spent, ';
                $updateData[':energy_spent'] = $energySpent;
            }
            if($energySpent < 0) {
                $sql .= 'energy_spent = 0, ';
            }
            if($rowWins >=0 ) {
                $sql .= 'row_wins = row_wins + :wins, ';
            } else {
                $sql .= 'row_wins = 0, ';
            }
            $sql .= 'wins         = wins + :wins,
                  battles      = battles + :battles,
                  level        = level + :level
                WHERE
                  id = :user_id AND
                  coins + :coins >= 0 AND
                  chips + :chips >= 0 AND
                  bucks + :bucks >= 0 AND
                  stamina + :stamina >= 0 AND
                  energy + :energy >= 0';
            $query = $db->prepare($sql);
            $query->execute($updateData);

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }
            return $response;
        }

        /**
         * Обновляем табличку user
         * @param int $userId
         * @param array $data
         * @return Response
         */
        public function updateUserAppearanceByUserId($userId, $data) {
            /** @var $db PDO */
            $db = $this->getDataBase();
            $response = new Response();
            $settings = $this->getSettingList();

            $updateData = array(
                ':user_id'      => $userId,
                ':bucks'        => $settings['change_character_price '],
                ':hair_id'      => isset($data['hair_id'])  ? $data['hair_id']  : 0,
                ':face_id'      => isset($data['face_id'])  ? $data['face_id']  : 0,
                ':nickname'     => isset($data['nickname']) ? $data['nickname'] : 'nickname');

            $sql =
                'UPDATE
                  user
                SET
                  bucks        = bucks - :bucks,
                  hair_id      = :hair_id,
                  face_id      = :face_id,
                  nickname     = :nickname
                WHERE
                  id = :user_id AND
                  bucks - :bucks >= 0';
            $query = $db->prepare($sql);
            $query->execute($updateData);

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }
            return $response;
        }

        /**
         * Выдаем пользователю деньги за соответствующее предложение
         * @param int $userId
         * @param int $offerId
         * @param int $credits
         * @return Response
         */
        public function giveOffer($userId, $offerId, $credits) {
            $response = new Response();
            $offer = OfferModel::getInstance()->getEntityByEntityId($offerId);
            if($offer->isError()) {
                return $offer;
            } else {
                $offer = $offer->getData();
            }
            if($offer['cost'] != $credits) {
                $response->setCode(Response::CODE_WRONG_DATA)->setError('Give Offer: wrong credits amount');
            }

            return $this->updateUserByUserId($userId, array('bucks' => $offer['bucks'] + $offer['bonus']));
        }

        /**
         * Выдаем пользователю плюхи за победу
         * @param int $userId
         * @param int $bet
         * @param int $opponent
         * @return Response
         */
        public function battleWin($userId, $bet, $opponent) {
            $winResult = $this->updateUserByUserId($userId, array(
                'coins'     => $bet,
                'row_wins'  => 1,
                'wins'      => 1,
                'battles'   => 1));

            if($winResult->isError()) {
                return $winResult;
            }

            if($bet && $opponent) {
                $looseResult = $this->updateUserByUserId($opponent, array(
                    'coins'     => -1 * $bet,
                    'row_wins'  => -1,
                    'battles'   => 1));

                if($looseResult->isError()) {
                    return $looseResult;
                }
            }

            return $this->getEntityByEntityId($userId);
        }

        /**
         * Выдаем пользователю плюхи за победу
         * @param int $userId
         * @param int $bet
         * @param int $opponent
         * @return Response
         */
        public function battleLose($userId, $bet, $opponent) {
            $winResult = $this->updateUserByUserId($userId, array(
                'coins'     => -1 * $bet,
                'row_wins'  => -1,
                'battles'   => 1));

            if($winResult->isError()) {
                return $winResult;
            }

            if($bet && $opponent) {
                $looseResult = $this->updateUserByUserId($opponent, array(
                    'coins'     => $bet,
                    'row_wins'  => 1,
                    'wins'      => 1,
                    'battles'   => 1));

                if($looseResult->isError()) {
                    return $looseResult;
                }
            }

            return $this->getEntityByEntityId($userId);
        }

        /**
         * Меняем валюту
         * @param int $userId
         * @param int $credits
         * @param string $currency
         * @return Response
         */
        public function buyCurrency($userId, $credits, $currency) {
            $settings = $this->getSettingList();

            return $this->updateUserByUserId($userId, array($currency => $credits * $settings[$currency . '_rate']));
        }

        /**
         * Удалить указанного пользователя
         * @param int $userId
         * @return Response
         */
        public function deleteUserByUserId($userId) {
            /** @var $db PDO */
            $db = $this->getDataBase();
            $response = new Response();

            $sql = 'DELETE FROM user WHERE id = :user_id LIMIT 1';
            $query = $db->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            $sql = 'DELETE FROM user_item WHERE user_id = :user_id';
            $query = $db->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            $sql = 'DELETE FROM user_step WHERE user_id = :user_id';
            $query = $db->prepare($sql);
            $query->execute(array(
                ':user_id' => $userId
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }

        /**
         * Восстанавливаем пользователю 1 энергию.
         * Вычисляем сколько прошло указанный периодов с последнего обновления и даем соответствующее количество энергии
         * @param int $userId
         * @return Response
         */
        public function restoreEnergy($userId) {
            /** @var $db PDO */
            $db = $this->getDataBase();
            $response = new Response();

            $sql =
                'UPDATE
                  ' . $this->_table . '
                SET
                  energy = energy + LEAST(TIMESTAMPDIFF(SECOND, energy_date, CURRENT_TIMESTAMP) DIV  energy_time, energy_max - energy),
                  energy_date = DATE_ADD(energy_date, INTERVAL (TIMESTAMPDIFF(SECOND, energy_date, CURRENT_TIMESTAMP) DIV energy_time) * energy_time SECOND)
                WHERE
                  id = :user_id';
            $query = $db->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId
            ));

            $err = $query->errorInfo();

            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }
            return $response;
        }

        /**
         * Восстанавливаем пользователю 5 мастерства.
         * Вычисляем сколько прошло указанный периодов с последнего обновления и даем соответствующее количество энергии
         * @param int $userId
         * @return Response
         */
        public function restoreStamina($userId) {
            /** @var $db PDO */
            $db = $this->getDataBase();
            $response = new Response();

            $sql =
                'UPDATE
                  ' . $this->_table . '
                SET
                  stamina = stamina + LEAST((TIMESTAMPDIFF(SECOND, stamina_date, CURRENT_TIMESTAMP) DIV  stamina_time) * 5, stamina_max - stamina),
                  stamina_date = DATE_ADD(stamina_date, INTERVAL (TIMESTAMPDIFF(SECOND, stamina_date, CURRENT_TIMESTAMP) DIV stamina_time) * stamina_time SECOND)
                WHERE
                  id = :user_id';
            $query = $db->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId
            ));

            $err = $query->errorInfo();

            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }
            return $response;
        }

        /**
         * Выдаем ежедневную награду пользователю
         * @param $userId int
         * @return Response
         */
        public function giveDailyAward($userId) {
            /** @var $db PDO */
            $db = $this->getDataBase();
            $response = new Response();

            //получаем текущие данные пользователя
            $user = $this->getEntityByEntityId($userId);
            if($user->isError()) {
                return $user;
            }
            $user = $user->getData();

            /**
             * смотрим какой у него сейчас день игры подряд и прошедшее время с момента выдачи последней награды
             * если прошло меньше суток - ничего не делаем
             * если больше 2х дней или прошел весь цикл - сбрасываем счетчик дней
             */
            if(strtotime($user['award_date']) < time() - 24*60*60 && strtotime($user['award_date']) > time() - 48*60*60) {
                $day = $user['award_day'] <= 6 ? $user['award_day'] + 1 : 1;
            } else if(strtotime($user['award_date']) < time() - 48*60*60) {
                $day = 1;
            } else {
                return $response;
            }

            //что мы должны дать пользователю за указанное количество дней
            $award = DailyAwardModel::getInstance()->getAwardByDay($day);
            if($award->isError()) {
                return $award;
            }
            $award = $award->getData();

            $awardResponse = $this->giveAward($userId, $award['award_id']);
            if($awardResponse->isError()) {
                return $awardResponse;
            }

            //выдаем награду, сохраняем дату получения награды, сохраняем количество дней
            $sql =
                'UPDATE
                  user
                SET
                  award_day = :day,
                  award_date = CURRENT_TIMESTAMP
                WHERE
                  id = :user_id';
            $query = $db->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':day'          => $day
            ));

            $err = $query->errorInfo();

            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }
            return $response->setData(array('award' => $awardResponse->getData()));
        }

        /**
         * Создать нового пользователя с указанным ID
         * @param $userId int
         * @param $faceId int
         * @param $hairId int
         * @param $nickname string
         * @return Response
         */
        public function addUserByUserId($userId, $faceId, $hairId, $nickname) {
            $settings = $this->getSettingList();
            /** @var $db PDO */
            $db = $this->getDataBase();
            $response = new Response();

            if(!$this->checkNickname($nickname)){
                $response->setCode(Response::CODE_ERROR)->setError('nickname_exist');
            }

            $sql = 'INSERT INTO ' . $this->_table . '
                   (id,
                    face_id,
                    hair_id,
                    nickname,
                    level,
                    energy,
                    energy_spent,
                    energy_max,
                    stamina,
                    stamina_max,
                    battles,
                    wins,
                    coins,
                    chips,
                    create_date,
                    award_date,
                    energy_date,
                    stamina_date,
                    energy_time,
                    stamina_time)
                VALUES
                   (:user_id,
                    :face_id,
                    :hair_id,
                    :nickname,
                    1,
                    :energy,
                    0,
                    :energy_max,
                    :stamina,
                    :stamina_max,
                    0,
                    0,
                    :coins,
                    :chips,
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP,
                    :energy_time,
                    :stamina_time)';
            $query = $db->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':face_id'      => $faceId,
                ':hair_id'      => $hairId,
                ':nickname'     => $nickname,
                ':energy'       => $settings['energy_max'],
                ':energy_max'   => $settings['energy_max'],
                ':stamina'      => $settings['stamina_max'],
                ':stamina_max'  => $settings['stamina_max'],
                ':coins'        => $settings['start_coins'],
                ':chips'        => $settings['start_chips'],
                ':energy_time'  => $settings['energy_time'],
                ':stamina_time' => $settings['stamina_time']
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            $response = UserSettingsModel::getInstance()->updateSettingsByUserId($userId, array('music' => 1, 'sfx' => 1, 'lang' => 'ru'));
            if($response->isError()) {
                return $response;
            }

            $response = UserItemModel::getInstance()->addUserItem($userId, $settings['start_body']);
            if($response->isError()) {
                return $response;
            }

            $response = UserItemModel::getInstance()->addUserItem($userId, $settings['start_head']);
            if($response->isError()) {
                return $response;
            }

            $response = UserItemModel::getInstance()->addUserItem($userId, $settings['start_hands']);
            if($response->isError()) {
                return $response;
            }

            $response = UserItemModel::getInstance()->addUserItem($userId, $settings['start_legs']);
            if($response->isError()) {
                return $response;
            }

            $response = UserItemModel::getInstance()->addUserItem($userId, $settings['start_shoes']);
            if($response->isError()) {
                return $response;
            }

            $response = UserItemModel::getInstance()->addUserItem($userId, $settings['start_music']);
            if($response->isError()) {
                return $response;
            }

            $response = UserItemModel::getInstance()->addUserItem($userId, $settings['start_cover']);
            return $response;
        }
    }
