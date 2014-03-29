<?php
    /**
     * работа с user_settings таблицей. настройки
     */
    class UserSettingsModel extends BaseModel {
        protected $_table = 'user_settings';

        /**
         * Создать самого себя
         *
         * @return UserSettingsModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Сохранение настроек
         * @param int $userId
         * @param array $settings
         * @return Response
         */
        public function updateSettingsByUserId($userId, $settings) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (id, music, sfx, lang, bet, turns)
                VALUES
                    (:user_id, :music, :sfx, :lang, :bet, :turns)
                ON DUPLICATE KEY UPDATE
                    music = :music,
                    sfx = :sfx,
                    lang = :lang,
                    bet = :bet,
                    turns = :turns';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'  => $userId,
                ':music'    => isset($settings['music']) ? $settings['music'] : 1,
                ':sfx'      => isset($settings['sfx']) ? $settings['sfx'] : 1,
                ':lang'     => isset($settings['lang']) ? $settings['lang'] : 'ru',
                ':bet'      => isset($settings['bet']) ? $settings['bet'] : 50,
                ':turns'      => isset($settings['turns']) ? $settings['turns'] : 1
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }

        /**
         * Получить список пользователей по уровням
         * @return Response
         */
        public function getUserMusicList() {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT music, count(id) as amount FROM ' . $this->_table . ' GROUP BY music';
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

        /**
         * Получить список пользователей по уровням
         * @return Response
         */
        public function getUserSfxList() {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT sfx, count(id) as amount FROM ' . $this->_table . ' GROUP BY sfx';
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
    }
