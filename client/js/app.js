fb = {
    /**
     * Постит сообщение на стене пользователя
     * @param picture - урл картинки
     * @param caption - заголовок
     * @param description - описание
     * @param actions - текст в линке в подвале сообщения
     */
    writeWall:function(picture, caption, description, actions) {
        FB.ui({
                method: 'feed',
                name: caption,
                picture: picture,
                description: description,
                actions: {name: actions, link: fb_params.url}
            },
            function(response) {
                if (response && response.post_id) {
                    thisMovie( "bubble01" ).jsonDataCallBack( '{"write":1}' );
                }
            });
    },
    /**
     * Зовет друзей в игру
     * @param message - сообщение
     */
    inviteFriends:function(title, message, to) {
        FB.ui({method: 'apprequests',
            filters: ['app_non_users'], //только тех, что не установили игру
            title: title,
            message: message,
            to: to,
            data: {type: 'invite', object_id: 'null'}
        }, fb.requestCallback);
    },
    /**
     * Покупка внутриигровой валюты за кредиты
     * @param title - Название валюты
     * @param description - описание валюты
     * @param price - цена в кредитах
     * @param image_url - картинка валюты
     * @param data - произвольные данныею в нашем случае - id предложения
     */
    placeOrder: function(type, title, description, price, image_url, data) {
        var order_info = {
            title: title,
            description: description,
            price: price,
            image_url: image_url,
            product_url: image_url,
            data: data,
            purchase_type: type
        };
        FB.ui({
            method: 'pay',
            action: 'buy_item',
            order_info: order_info
        }, fb.creditsCallback);
    },
    creditsCallback: function(data) {
        if (data['order_id']) {	
            thisMovie( "bubble01" ).jsonDataCallBack( '{"type":"buyDiamonds","success":1}' );
            return true;
        } 
        else {
            thisMovie( "bubble01" ).jsonDataCallBack( '{"type":"buyDiamonds","success":0}' );
            return false;
        }
    },
    sendRequestToRecipients: function(message, to, exclude_ids, type, object_id) {
        FB.ui({method: 'apprequests',
            message: message,
            to: to,
            exclude_ids: exclude_ids,
            data: {type: type, object_id: object_id}
        }, fb.requestCallback);
    },
    requestCallback: function(data) {
        $.getJSON('/server/index.php/request/save?request_id=' + data.request + '&recipients=' + data.to.join(','));
    }
}

//
// Additional functions
//

function thisMovie(movieName) {
    if (window.document[movieName])
        return window.document[movieName];

    if (navigator.appName.indexOf('Microsoft Internet') == -1)
    {
        if (document.embeds && document.embeds[movieName])
            return document.embeds[movieName];
    }
    else
        return document.getElementById(movieName);
}
