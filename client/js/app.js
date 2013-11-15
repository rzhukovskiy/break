social = {
    /**
     * Постит сообщение на стене пользователя
     * @param message - сообщение
     */
    writeWall:function(message) {
        VK.api("wall.post", {message: message, test_mode: 1}, function(response) {
            thisMovie("application").sendFromJS( JSON.stringify(response) );
        });
    },
    /**
     * Зовет друзей в игру
     */
    inviteFriends:function() {
        VK.callMethod("showInviteBox");
    },
    /**
     * Список всех друзей
     */
    getUser:function(uids) {
        VK.api("users.get", {uids: uids, fields: 'photo_medium', test_mode: 1}, function(response) {
            thisMovie("application").sendFromJS( JSON.stringify(response) );
        });
    },
    /**
     * Получить альбомы
     */
    getAlbums:function() {
        VK.api("photos.getAlbums", {}, function(response) {
            thisMovie("application").sendFromJS( JSON.stringify(response) );
            console.log(response);
        });
    },
    /**
     * Создать альбом
     */
    createAlbum:function(title, description) {
        VK.api("photos.createAlbum", {title: title, description: description, test_mode: 1}, function(response) {
            thisMovie("application").sendFromJS( JSON.stringify(response) );
        });
    },
    /**
     * Получить сервер
     */
    getServer:function(album_id) {
        VK.api("photos.getUploadServer", {album_id: album_id, test_mode: 1}, function(response) {
            thisMovie("application").sendFromJS( JSON.stringify(response) );
        });
    },
    /**
     * Сохранить фото
     */
    savePhoto:function(album_id, server, photos_list, hash) {
        VK.api(" photos.save", {album_id: album_id, server: server, photos_list: photos_list, hash: hash, test_mode: 1}, function(response) {
            thisMovie("application").sendFromJS( JSON.stringify(response) );
        });
    },
    /**
     * Список всех друзей
     */
    getAllFriends:function() {
        VK.api("friends.get", {fields: 'photo_medium', test_mode: 1}, function(response) {
            thisMovie("application").sendFromJS( JSON.stringify(response) );
        });
    },
    /**
     * Список друзей в игре
     */
    getAppFriends:function() {
        VK.api("execute", {code: 'return API.getProfiles({"uids":API.getAppFriends(), "fields": "photo_medium", "test_mode": 1});', test_mode: 1}, function(response) {
            thisMovie("application").sendFromJS( JSON.stringify(response) );
        });
    },
    /**
     * Покупка внутриигровой валюты за кредиты
     * @param item - ид оффера
     */
    placeOrder: function(item) {
        var params = {
            type: 'item',
            item: item
        };
        VK.callMethod('showOrderBox', params);
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
