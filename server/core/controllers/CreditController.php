<?php
    /**
     * Контроллер для работы с кредитами фейсбука
     */
    class CreditController extends BaseController {
        /**
         * Сюда приходит колбэк от фб
         * @return bool
         */
        function paymentAction() {
            // prepare the return data array
            $data = array('content' => array());

            // parse signed data
            $fbParams = $this->_globals->getParam('facebook');
            $request = $this->parseSignedRequest($_REQUEST['signed_request'], $fbParams['secret']);

            if ($request == null)
                return false;

            $payload = $request['credits'];

            // retrieve all _params passed in
            $func = $_REQUEST['method'];
            $order_id = $payload['order_id'];

            if ($func == 'payments_status_update') {
                $status = $payload['status'];

                if ($status == 'placed') {
                    $order_details = json_decode($payload['order_details'], true);

                    //получаем тип покупки и id предмета
                    $purchaseData = json_decode($order_details['items'][0]['data'], true);

                    $response = new Response();
                    switch($purchaseData['purchase_type']) {
                        case 'offer':
                            $response = UserModel::getInstance()->giveOffer($request['user_id'], $purchaseData['object_id'], $order_details['amount']);
                            break;
                        case 'currency':
                            $response = UserModel::getInstance()->buyCurrency($request['user_id'], $order_details['amount'], $purchaseData['object_id']);
                            break;
                        case 'amulet':
                            $response = UserAmuletModel::getInstance()->buyUserAmuletForCredits($request['user_id'], $purchaseData['object_id'], $order_details['amount']);
                            break;
                        case 'chest':
                            $response = UserChestModel::getInstance()->openChestForKeys($request['user_id'], $purchaseData['object_id'], $order_details['amount']);
                            break;
                        default:
                            $response->setCode(Response::CODE_WRONG_DATA)->setError('Wrong purchase type');
                    }

                    if($response->isError()) {
                        $next_state = 'canceled';
                    } else {
                        $next_state = 'settled';
                    }

                    $data['content']['status'] = $next_state;
                }
                // compose returning data array_change_key_case
                $data['content']['order_id'] = $order_id;
            }
            else if ($func == 'payments_get_items') {
                // remove escape characters
                $order_info = stripcslashes($payload['order_info']);
                $item = json_decode($order_info, true);

                $item['price'] = (int)$item['price'];
                $item['data'] = json_encode(array(
                    'purchase_type' => $item['purchase_type'],
                    'object_id'     => $item['data']
                ));

                // for url fields, if not prefixed by http://, prefix them
                $url_key = array('product_url', 'image_url');
                foreach ($url_key as $key)
                {
                    if (substr($item[$key], 0, 7) != 'http://')
                    {
                        $item[$key] = 'http://'.$item[$key];
                    }
                }

                // prefix test-mode
                if (isset($payload['test_mode']))
                {
                    $update_keys = array('title', 'description');
                    foreach ($update_keys as $key)
                    {
                        $item[$key] = '[Test Mode] '.$item[$key];
                    }
                }
                $data['content'] = array($item);
            }

            // required by api_fetch_response()
            $data['method'] = $func;

            // send data back
            echo json_encode($data);
            exit();
        }

        private function parseSignedRequest($signed_request, $secret)
        {
            list($encoded_sig, $payload) = explode('.', $signed_request, 2);

            // decode the data
            $sig = $this->base64UrlDecode($encoded_sig);
            $data = json_decode($this->base64UrlDecode($payload), true);

            if (strtoupper($data['algorithm']) !== 'HMAC-SHA256')
            {
                //error_log('Unknown algorithm. Expected HMAC-SHA256');
                return null;
            }

            // check signature
            $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
            if ($sig !== $expected_sig)
            {
                //error_log('Bad Signed JSON signature!');
                return null;
            }

            return $data;
        }

        private function base64UrlDecode($input)
        {
            return base64_decode(strtr($input, '-_', '+/'));
        }

        protected function getRemoteData($url)
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            return json_decode($result, true);
        }

        protected function postRemoteData($url, $data)
        {
            $fields_string = '';
            foreach($data as $key=>$value) {
                $fields_string .= $key.'='.$value.'&';
            }
            rtrim($fields_string, '&');

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST,           true);
            curl_setopt($ch, CURLOPT_POSTFIELDS,     $fields_string);
            $result = curl_exec($ch);
            curl_close($ch);
            return json_decode($result, true);
        }
    }
