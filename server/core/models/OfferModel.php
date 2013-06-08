<?php
    /**
     * Работа с таблицей offer
     */
    class OfferModel extends BaseModel {
        /**
         * Создать самого себя
         *
         * @return OfferModel
         */
        public static function getInstance() {
            return parent::getInstance();
        }

        /**
         * Получить предложение по номеру группы и количеству кредитов
         * @param int $offerId
         * @return Response
         */
        public function getOfferByOfferId($offerId) {
            /** @var $gameDb PDO */
            $gameDb = $this->getGameBase();
            $response = new Response();

            $sql = 'SELECT * FROM offer WHERE id = :id LIMIT 1';
            $query = $gameDb->prepare($sql);
            $query->execute(array(
                ':id' => $offerId
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
