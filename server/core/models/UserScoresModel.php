<?php
    /**
     * работа с user_scores таблицей. очки в мини-играх
     */
    class UserScoresModel extends BaseModel {
        protected $_table = 'user_scores';

        /**
         * Создать самого себя
         *
         * @return UserScoresModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Очки в разных играх у определенного пользователя
         * @param int $userId
         * @return Response
         */
        public function getUserScoresListByUserId($userId) {
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
         * Сохранить очки
         * @param int $userId
         * @param string $gameId
         * @param int $scores
         * @return Response
         */
        public function saveUserScores($userId, $gameId, $scores) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            $sql =
                'INSERT INTO
                    ' . $this->_table . '
                    (user_id, game_id, scores, create_date)
                VALUES
                    (:user_id, :game_id, :scores, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    scores = GREATEST(scores, :scores)';
            $query = $dataDb->prepare($sql);
            $query->execute(array(
                ':user_id'      => $userId,
                ':game_id'      => $gameId,
                ':scores'       => $scores
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            }

            return $response;
        }

        /**
         * Получение топа
         * @param int $amount
         * @param int $days
         * @return Response
         */
        public function getTopUserList($amount, $days = 1) {
            /** @var $dataDb PDO */
            $dataDb = $this->getDataBase();
            $response = new Response();

            if($days < 32) {
                $sql =
                    'SELECT
                        *
                    FROM
                        ' . $this->_table . '
                    WHERE
                         modify_date > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL :days DAY)
                    ORDER BY
                        scores DESC
                    LIMIT :amount';
                $query = $dataDb->prepare($sql);
                $query->bindValue(':amount', (int)$amount, PDO::PARAM_INT);
                $query->bindValue(':days', $days, PDO::PARAM_INT);
                $query->execute();
            } else {
                $sql =
                    'SELECT
                        *
                    FROM
                        ' . $this->_table . '
                    WHERE scores > 0
                    ORDER BY
                        scores DESC
                    LIMIT :amount';
                $query = $dataDb->prepare($sql);
                $query->bindValue(':amount', (int)$amount, PDO::PARAM_INT);
                $query->execute();
            }

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($query->fetchAll(PDO::FETCH_ASSOC));
            }
            return $response;
        }
    }
