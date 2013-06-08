<?php
    /**
     * Работа с таблицей award
     */
    class AwardModel extends BaseModel {
        /**
         * Создать самого себя
         *
         * @return AwardModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Получить авард по ID
         * @param string $awardId - ид получаемого аварда
         * @return Response
         */
        public function getAwardByAwardId($awardId) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT * FROM award WHERE id = :award_id LIMIT 1';
            $query = $gameDb->prepare($sql);
            $query->execute(array(
                ':award_id' => $awardId
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
