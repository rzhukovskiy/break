<?php
    /**
     * Контроллер отвечающий за работу с запросами пользователей
     */
    class RequestController extends BaseController {
        /**
         * Сохраняем запрос
         * request_id - ID запроса (required)
         */
        public function saveAction() {
            $requestInfo = $this->_social->getRequestInfo($this->getRequest()->getParam('request_id'));
            $requestData = json_decode($requestInfo['data'], true);
            $response = new Response();

            foreach(explode(',', $this->getRequest()->getParam('recipients')) as $recipient) {
                $data = array(
                    'id'            => $requestInfo['id'],
                    'user_id_from'  => $this->_social->getUserId(),
                    'user_id_to'    => $recipient,
                    'type'          => $requestData['type'],
                    'object_id'     => $requestData['object_id']
                );

                $response = RequestModel::getInstance()->saveRequest($data);
            }

            $response->send();
        }

        /**
         * Принимаем запрос
         * request_id - ID запроса (required)
         */
        public function acceptAction() {
            $this->_social->deleteRequest($this->getRequest()->getParam('request_id'), $this->_social->getUserId());
            RequestModel::getInstance()->acceptRequest($this->getRequest()->getParam('request_id'), $this->_social->getUserId())->send();
        }
    }
