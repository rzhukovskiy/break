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
                    (id, music, sfx, lang)
                VALUES
                    (:user_id, :music, :sfx, :lang)
                ON DUPLICATE KEY UPDATE
                    music = :music,
                    sfx = :sfx,
                    lang = :lang';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'  => $userId,
                ':music'    => isset($settings['music']) ? $settings['music'] : 1,
                ':sfx'      => isset($settings['sfx']) ? $settings['sfx'] : 1,
                ':lang'     => isset($settings['lang']) ? $settings['lang'] : 'ru'
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }
    }
