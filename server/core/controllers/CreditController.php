<?php
    /**
     * Контроллер для работы с голосами вк
     */
    class CreditController extends BaseController {
        /**
         * Сюда приходит колбэк от вк
         * @return bool
         */
        function paymentAction() {
            $vkConfig = $this->_globals->getParam('vk');
            $input = $_POST;
            // Проверка подписи
            $sig = $input['sig'];
            unset($input['sig']);
            ksort($input);
            $str = '';
            foreach ($input as $k => $v) {
                $str .= $k.'='.$v;
            }

            if ($sig != md5($str . $vkConfig['api_secret'])) {
                $response['error'] = array(
                    'error_code' => 10,
                    'error_msg' => 'Несовпадение вычисленной и переданной подписи запроса.',
                    'critical' => true
                );
            } else {
                // Подпись правильная
                switch ($input['notification_type']) {
                    case 'get_item':
                        // Получение информации о товаре
                        $offerId = $input['item']; // ид офера

                        $offer = OfferModel::getInstance()->getEntityByEntityId($offerId);

                        if (!$offer->isError()) {
                            $offer = $offer->getData();
                            $response['response'] = array(
                                'item_id' => $offerId,
                                'title' => ($offer['bucks'] + $offer['bonus']) . ' баксов',
                                'photo_url' => 'http://somesite/images/coin.jpg',
                                'price' => $offer['cost']
                            );
                        } else {
                            $response['error'] = array(
                                'error_code' => 20,
                                'error_msg' => 'Товара не существует.',
                                'critical' => true
                            );
                        }
                        break;

                    case 'get_item_test':
                        // Получение информации о товаре
                        $offerId = $input['item']; // ид офера

                        $offer = OfferModel::getInstance()->getEntityByEntityId($offerId);

                        if (!$offer->isError()) {
                            $offer = $offer->getData();
                            $response['response'] = array(
                                'item_id' => $offerId,
                                'title' => ($offer['bucks'] + $offer['bonus']) . ' баксов',
                                'photo_url' => 'http://somesite/images/coin.jpg',
                                'price' => $offer['cost']
                            );
                        } else {
                            $response['error'] = array(
                                'error_code' => 20,
                                'error_msg' => 'Товара не существует.',
                                'critical' => true
                            );
                        }
                        break;

                    case 'order_status_change':
                        // Изменение статуса заказа
                        if ($input['status'] == 'chargeable') {
                            $order_id = intval($input['order_id']);

                            // Код проверки товара, включая его стоимость
                            $app_order_id = 1; // Получающийся у вас идентификатор заказа.

                            $offerResult = UserModel::getInstance()->giveOffer($input['receiver_id'], $input['item_id'], $input['item_price']);
                            if($offerResult->isError()) {
                                $response['error'] = array(
                                    'error_code' => 21,
                                    'error_msg' => 'Неверная цена.',
                                    'critical' => true
                                );
                            } else {
                                $response['response'] = array(
                                    'order_id' => $order_id,
                                    'app_order_id' => $app_order_id,
                                );
                            }
                        } else {
                            $response['error'] = array(
                                'error_code' => 100,
                                'error_msg' => 'Передано непонятно что вместо chargeable.',
                                'critical' => true
                            );
                        }
                        break;

                    case 'order_status_change_test':
                        // Изменение статуса заказа в тестовом режиме
                        if ($input['status'] == 'chargeable') {
                            $order_id = intval($input['order_id']);

                            // Код проверки товара, включая его стоимость
                            $app_order_id = 1; // Получающийся у вас идентификатор заказа.

                            $offerResult = UserModel::getInstance()->giveOffer($input['receiver_id'], $input['item_id'], $input['item_price']);

                            if($offerResult->isError()) {
                                $response['error'] = array(
                                    'error_code' => 21,
                                    'error_msg' => 'Неверная цена.',
                                    'critical' => true
                                );
                            } else {
                                $response['response'] = array(
                                    'order_id' => $order_id,
                                    'app_order_id' => $app_order_id,
                                );
                            }
                        } else {
                            $response['error'] = array(
                                'error_code' => 100,
                                'error_msg' => 'Передано непонятно что вместо chargeable.',
                                'critical' => true
                            );
                        }
                        break;
                }
            }

            echo json_encode($response);
        }
    }
