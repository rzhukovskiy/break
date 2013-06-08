<?php
    /**
     * Работа с таблицей DailyAward
     */
    class DailyAwardModel extends BaseModel {
        /**
         * Создать самого себя
         *
         * @return DailyAwardModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Получает награду, которая полагается за указанный день игры подряд
         * @param int $day - день
         * @return Response
         */
        public function getAwardByDay($day) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT * FROM daily_award WHERE day = :day LIMIT 1';
            $query = $gameDb->prepare($sql);
            $query->execute(array(
                ':day' => $day
            ));

            $err = $query->errorInfo();
            if($err[1] != null){
                $response->setCode(Response::CODE_ERROR)->setError($err[2]);
            } else {
                $response->setData($query->fetch(PDO::FETCH_ASSOC));
            }
            return $response;
        }
    }
