<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <script type="text/javascript" src="../client/js/app.js?v=00.01.001"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>

        <script src="http://vk.com/js/api/xd_connection.js?2"  type="text/javascript">
        </script>

        <script type="text/javascript">
            var app;
            var flashvars;
            var vk_id;
            var auth_key;

            VK.apiId = <?php echo $vk['app_id'] ?>;

            window.onload = (function() {
                VK.init(function() {
                    try {
                        var flashvars = new Array();
                        flashvars.viewer_id = <?php echo $_REQUEST['viewer_id'] ?>;
                        flashvars.auth_key = <?php echo $_REQUEST['auth_key'] ?>;
                        flashvars.isLocal = "0";

                        var d = new Date();
                        flashvars.time = d.getTime();

                        var params = { menu: "false", wmode: "Opaque", scale: "noscale", allowFullscreen: "true", allowNetworking : "all", allowScriptAccess: "always", bgcolor: "#000000" };
                        var attributes = { id: "application", name: "application" };
                        swfobject.embedSWF("https://zluki.com/break/client/game.swf", "alt-content", "100%", '815px', "10.1.0", false, flashvars, params, attributes, function(e) {
                            app = e.ref;
                        });
                    } catch (e) {
                        txt = "There was an error on this page.\n\n";
                        txt += "VK.init function\n\n";
                        txt += "Error description: " + e + "\n\n";
                        txt += "Click OK to continue.\n\n";
                        console.log(txt);
                    }
                });
            });
        </script>
    </head>

    <body>
    <div class="tests">
        <a href="http://zluki.com/break/server/index.php/user/get?uid=<?php echo $_REQUEST['viewer_id'] ?>&auth_key=<?php echo $_REQUEST['auth_key'] ?>" target="_blank">Get user</a> |
        <a href="http://zluki.com/break/server/index.php/user/add?hair_id=red&face_id=ugly&uid=<?php echo $_REQUEST['viewer_id'] ?>&auth_key=<?php echo $_REQUEST['auth_key'] ?>" target="_blank">Add user</a> |
        <a href="http://zluki.com/break/server/index.php/user/delete?uid=<?php echo $_REQUEST['viewer_id'] ?>&auth_key=<?php echo $_REQUEST['auth_key'] ?>" target="_blank">Delete user</a> |
        <a href="http://zluki.com/break/server/index.php/user/startMission" target="_blank">Start mission</a> |
        <a href="http://zluki.com/break/server/index.php/user/progress?mission_id=loc1mis1&scores=31000" target="_blank">Save progress</a> |
        <a href="#" onclick="fb.placeOrder('amulet', 'Amulet', 'Cool amulet', 30, '', 'accuracy')">Credits test</a> |
        <a href="#" onclick="fb.writeWall('', 'Test note on the wall', 'This is just a test', 'Play the game!')">Write wall</a> |
        <a href="#" onclick="fb.inviteFriends('Invite!', 'Come get some!', '581607783,636994392')">Invite friends</a> |
        <a href="#" onclick="fb.sendRequestToRecipients('Help me!', '', '', 'location', 'loc1')">Send request</a> |
        <a href="#" onclick="fb.placeOrder('currency', 'Coins', 'Cool coins', 33, '', 'coins')">Coins for credits</a>
    </div>
    <div id="altContent">
        <h1>Breakdance</h1>
        <p><a href="http://www.adobe.com/go/getflashplayer">Get Adobe Flash player</a></p>
    </div>
</body>
</html>
