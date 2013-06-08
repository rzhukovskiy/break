<?php
    /**
     * Работа с таблицей mission
     */
    class MissionModel extends BaseModel {
        /**
         * Создать самого себя
         *
         * @return MissionModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Получает миссию по ID
         * @param string $missionId
         * @return Response
         */
        public function getMissionByMissionId($missionId) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT * FROM mission WHERE id = :mission_id LIMIT 1';
            $query = $gameDb->prepare($sql);
            $query->execute(array(
                ':mission_id' => $missionId
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
