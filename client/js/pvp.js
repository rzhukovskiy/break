pvp = {
    channels: [],
    listener: function(url, successCallback, failureCallback) {
        var scope = this;
        var etag = 0;
        var lastModified = LOAD_TIME;

        var launched = false;

        var failure = false;

        var stopped = false;

        this.successTimeout = 1000;
        this.failureTimeout = 0;

        var getTimeout = function () {
            return failure ? this.failureTimeout : this.successTimeout;
        };

        var listen = function () {
            if(!stopped) {
                $.ajax(scope.ajaxOptions);
            }
        }

        var beforeSend = function (jqXHR) {
            jqXHR.setRequestHeader("If-None-Match", etag);
            jqXHR.setRequestHeader("If-Modified-Since", lastModified);
        };
        var complete = function (jqXHR) {
            var timeout = getTimeout();
            if (jqXHR.getResponseHeader('Etag') != null && jqXHR.getResponseHeader('Last-Modified') != null) {
                etag = jqXHR.getResponseHeader('Etag');
                lastModified = jqXHR.getResponseHeader('Last-Modified');
            }
            var timeout = jqXHR.statusText == 'success' ? scope.successTimeout : scope.failureTimeout;

            if (timeout > 0) {
                setTimeout(listen, timeout);
            } else {
                listen();
            }
        };
        this.ajaxOptions = {
            url : url,
            type : 'GET',
            async : true,
            error : failureCallback,
            success : successCallback,
            dataType : 'json',
            complete : complete,
            beforeSend : beforeSend,
            timeout: 1000 * 20
        };
        this.start = function (timeout) {
            if (!launched) {
                if (typeof(timeout) == 'undefined' || timeout == 0) {
                    listen();
                } else {
                    setTimeout(listen, timeout);
                }
                launched = true;
            }
        };
        this.stop = function () {
            stopped = true;
        }
        this.start();
    },
    onSuccess: function(data) {
        if (data) {
            $("#messages").prepend(data.message + "<br />");
        } else {
            console.log("EMPTY DATA");
        }
    },
    onError: function() {
        console.log("ERROR");
    },
    stopListener: function() {
        this.listener.stop();
    },
    sendMessage: function(cid, text) {
        $.post('http://zluki.com/pub?cid=' + cid, '{"message": "' + text + '"}');
    }
}