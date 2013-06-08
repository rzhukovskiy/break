<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"'); ?>

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta property="vk:app_id" content="<?php echo $vk['app_id'] ?>" />
    <script type="text/javascript" src="http://userapi.com/js/api/openapi.js?48"></script>
    <script type="text/javascript" src="../client/js/swfobject.js"></script>
    <script type="text/javascript" src="../client/js/app.js?v=00.01.001"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>

    <!--<script src="/client/vk/js/xd_connection.js" type="text/javascript"></script>-->

    <style type="text/css">
        html, body { height: 100%; overflow: hidden }
        body { margin: 0 }
    </style>

    <script type="text/javascript">
        var app;
        var flashvars;
        var vk_id;
        var auth_key;

        VK._apiId = <?php echo $vk['app_id'] ?>;

        window.onload = (function() {
            VK.init(function() {
                try {
                <?php $_GET['api_result'] = str_replace('"', '%22', stripslashes($_GET['api_result'])) ?>
                    flashvars = <?php echo json_encode($_GET) ?>;

                    flashvars.resUrl = "../../resources/gui.swf";
                    flashvars.isLocal = "0";

                    vk_id = flashvars.viewer_id;
                    auth_key = flashvars.auth_key;

                    var d = new Date();
                    flashvars.time = d.getTime();

                    var params = { menu: "false", wmode: "Opaque", scale: "noscale", allowFullscreen: "true", allowNetworking : "all", allowScriptAccess: "always", bgcolor: "#000000" };
                    var attributes = { id: "application", name: "application" };
                    swfobject.embedSWF("http://cs6180.vk.com/u130504063/3609aefc9d82fc.zip"/*"Carsclient.swf?" + Math.ceil(Math.random()*10000)*/, "alt-content", "100%", '815px', "10.1.0", false, flashvars, params, attributes, function(e) {
                        app = e.ref;
                    });
                } catch (e) {
                    txt = "There was an error on this page.\n\n";
                    txt += "VK.init function\n\n";
                    txt += "Error description: " + e + "\n\n";
                    txt += "Click OK to continue.\n\n";
                    alert(txt);
                }
            });
        });


        function api(method, params, id) {
            VK.api(method, params, function (response) {
                try {
                    app.api(response, id);
                } catch (e) {
                    txt = "There was an error on this page.\n\n";
                    txt += "Error description: " + e + "\n\n";
                    txt += "api function. trying to do "+method+" method with params:"+params+" and id:"+id+"\n\nresponce="+response;
                    txt += "Click OK to continue.\n\n";
                    alert(txt);
                }
            });
        }



        function invite ()
        {
            VK.callMethod("showInviteBox");
        }

        function showRequestBox (id, message, key)
        {
            VK.callMethod("showRequestBox", id, message, key);
        }

        function call_method(method, params) {
            VK.callMethod(method, params);
        }

        VK.addCallback("onWindowBlur", function() {
            //app.style.visibility = 'hidden';
            VK.callMethod("resizeWindow", 815, 1);
        });

        VK.addCallback("onWindowFocus", function() {
            //app.style.visibility = 'visible';
            VK.callMethod("resizeWindow", 815, 645);
        });

        VK.addCallback("onBalanceChanged", function(balance) {
            app.event("onBalanceChanged", balance);
        });

        VK.addCallback("?onWallPostSave", function() {
            app.event("?onWallPostSave", null);
        });

        VK.addCallback("onWallPostCancel", function() {
            app.event("onWallPostCancel", null);
        });

        VK.addCallback("onRequestSuccess", function() {
            app.event("onRequestSuccess", null);
        });

        VK.addCallback("onRequestCancel", function() {
            app.event("onRequestCancel", null);
        });

        VK.addCallback("onRequestFail", function() {
            app.event("onRequestFail", null);
        });

        VK.addCallback("onToggleFlash", function() {
            app.event("onToggleFlash", true);
        });


    </script>
</head>

<body>
    <div class="tests">
        <a href="http://bubble.battlekeys.com/server/index.php/user/get" target="_blank">Get user</a> |
        <a href="http://bubble.battlekeys.com/server/index.php/user/delete" target="_blank">Delete user</a> |
        <a href="http://bubble.battlekeys.com/server/index.php/user/startMission" target="_blank">Start mission</a> |
        <a href="http://bubble.battlekeys.com/server/index.php/user/progress?mission_id=loc1mis1&scores=31000" target="_blank">Save progress</a> |
        <a href="#" onclick="fb.placeOrder('amulet', 'Amulet', 'Cool amulet', 30, '', 'accuracy')">Credits test</a> |
        <a href="#" onclick="fb.writeWall('', 'Test note on the wall', 'This is just a test', 'Play the game!')">Write wall</a> |
        <a href="#" onclick="fb.inviteFriends('Invite!', 'Come get some!', '581607783,636994392')">Invite friends</a> |
        <a href="#" onclick="fb.sendRequestToRecipients('Help me!', '', '', 'location', 'loc1')">Send request</a> |
        <a href="#" onclick="fb.placeOrder('currency', 'Coins', 'Cool coins', 33, '', 'coins')">Coins for credits</a>
    </div>
    <div id="altContent">
        <h1>bubble0.1</h1>
        <p><a href="http://www.adobe.com/go/getflashplayer">Get Adobe Flash player</a></p>
    </div>
</body>
</html>
