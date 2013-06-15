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

            $response = $this->updateUserByUserId($userId, array(
                'coins'     => $award['coins'],
                'energy'    => $award['energy'],
            ));

            if($response->isError()) {
                return $response;
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

            $sql =
                'UPDATE
                  user
                SET
                  coins        = coins + :coins,
                  energy       = LEAST(energy + :energy, energy_max),
                  energy_max   = energy_max + :energy_max,
                  stamina      = LEAST(stamina + :stamina, stamina_max),
                  stamina_max  = stamina_max + :stamina_max,
                  energy_time  = energy_time + :energy_time,
                  stamina_time = stamina_time + :stamina_time,
                  spent_energy = spent_energy + :spent_energy
                WHERE
                  id = :user_id AND
                  coins + :coins >= 0 AND
                  stamina + :stamina >= 0 AND
                  energy + :energy >= 0';
            $query = $db->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':coins'        => isset($data['coins']) ? $data['coins'] : 0,
                ':energy'       => isset($data['energy']) ? $data['energy'] : 0,
                ':energy_max'   => isset($data['energy_max']) ? $data['energy_max'] : 0,
                ':stamina'      => isset($data['stamina']) ? $data['stamina'] : 0,
                ':stamina_max'  => isset($data['stamina_max']) ? $data['stamina_max'] : 0,
                ':energy_time'  => isset($data['energy_time']) ? $data['energy_time'] : 0,
                ':stamina_time' => isset($data['stamina_time']) ? $data['stamina_time'] : 0,
                ':spent_energy' => isset($data['spent_energy']) ? $data['spent_energy'] : 0
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }
            if($query->rowCount() < 1) {
                $response->setCode(Response::CODE_WRONG_DATA)->setError('Not enough resources');
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
            $offer = OfferModel::getInstance()->getOfferByOfferId($offerId);
            if($offer->isError()) {
                return $offer;
            } else {
                $offer = $offer->getData();
            }
            if($offer['credits'] != $credits) {
                $response->setCode(Response::CODE_WRONG_DATA)->setError('Give Offer: wrong credits amount');
            }

            return $this->giveAward($userId, $offer['award_id']);
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
                  energy = energy + LEAST(TIMESTAMPDIFF(MINUTE, energy_date, CURRENT_TIMESTAMP) DIV  energy_time, energy_max - energy),
                  energy_date = DATE_ADD(energy_date, INTERVAL (TIMESTAMPDIFF(MINUTE, energy_date, CURRENT_TIMESTAMP) DIV energy_time) * energy_time MINUTE)
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
                  stamina = stamina + LEAST((TIMESTAMPDIFF(MINUTE, stamina_date, CURRENT_TIMESTAMP) DIV  stamina_time) * 5, stamina_max - stamina),
                  stamina_date = DATE_ADD(stamina_date, INTERVAL (TIMESTAMPDIFF(MINUTE, stamina_date, CURRENT_TIMESTAMP) DIV stamina_time) * stamina_time MINUTE)
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
                $day = $user['award_day'] <= 4 ? $user['award_day'] + 1 : 1;
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
         * @return Response
         */
        public function addUserByUserId($userId, $faceId, $hairId) {
            $settings = $this->getSettingList();
            /** @var $db PDO */
            $db = $this->getDataBase();
            $response = new Response();

            $sql = 'INSERT INTO ' . $this->_table . '
                   (id,
                    face_id,
                    hair_id,
                    exp,
                    level,
                    energy,
                    spent_energy,
                    energy_max,
                    stamina,
                    stamina_max,
                    battles,
                    wins,
                    coins,
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
                    0,
                    1,
                    :energy,
                    0,
                    :energy_max,
                    :stamina,
                    :stamina_max,
                    0,
                    0,
                    :coins,
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
                ':energy'       => $settings['energy_max'],
                ':energy_max'   => $settings['energy_max'],
                ':stamina'      => $settings['stamina_max'],
                ':stamina_max'  => $settings['stamina_max'],
                ':coins'        => $settings['start_coins'],
                ':energy_time'  => $settings['energy_time'],
                ':stamina_time' => $settings['stamina_time']
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($db->lastInsertId());
            }
            return $response;
        }
    }
