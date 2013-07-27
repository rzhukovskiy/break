<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <script type="text/javascript" src="../client/js/app.js?v=<?php echo time(); ?>"></script>
        <script type="text/javascript" src="../client/js/pvp.js?v=<?php echo time(); ?>"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>

        <script src="http://vk.com/js/api/xd_connection.js?2"  type="text/javascript">
        </script>

        <script type="text/javascript">
            <?php
                $_REQUEST['viewer_id']  = isset($_REQUEST['viewer_id']) ? $_REQUEST['viewer_id'] : 1;
                $_REQUEST['auth_key']   = isset($_REQUEST['auth_key']) ? $_REQUEST['auth_key'] : 1;
                $_REQUEST['api_result'] = isset($_REQUEST['api_result']) ? $_REQUEST['api_result'] : '';
            ?>
            var LOAD_TIME = "<?=date('r');?>";
            var flashvars = new Array();
            flashvars.uid = <?php echo $_REQUEST['viewer_id'] ?>;
            flashvars.auth_key = '<?php echo $_REQUEST['auth_key'] ?>';
            flashvars.user_info = '<?php echo json_encode($_REQUEST['api_result']) ?>';
            flashvars.isLocal = "0";

            var d = new Date();
            flashvars.time = d.getTime();

            var params = {
                base: "http://zluki.com/break/client/",
                menu: "false",
                wmode: "Opaque",
                scale: "noscale",
                allowFullscreen: "true",
                allowNetworking : "all",
                allowScriptAccess: "always",
                bgcolor: "#000000" };
            var attributes = { id: "application", name: "application" };
            swfobject.embedSWF(
                    "http://zluki.com/break/client/Main.swf?"+Math.floor(Math.random()*65535),
                    "altContent", "790", "615", "11.4.0",
                    false,
                    flashvars, params, attributes);

            VK.apiId = <?php echo $vk['app_id'] ?>;

            window.onload = (function() {
                VK.init();
            });
        </script>
    </head>

    <body>
    <div id="altContent">
        <a href="http://www.adobe.com/go/getflashplayer">
            <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
        </a>
    </div>

    <div class="tests">
        <a href="http://zluki.com/break/server/index.php/user/get?viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&auth_key=<?php echo $_REQUEST['auth_key'] ?>" target="_blank">Get user</a> |
        <a href="http://zluki.com/break/server/index.php/user/add?hair_id=1&face_id=1&viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&auth_key=<?php echo $_REQUEST['auth_key'] ?>" target="_blank">Add user</a> |
        <a href="http://zluki.com/break/server/index.php/user/delete?viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&auth_key=<?php echo $_REQUEST['auth_key'] ?>" target="_blank">Delete user</a> |
        <a href="http://zluki.com/break/server/index.php/user/learnStep?energy_spent=100&step_id=indian_step&viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&auth_key=<?php echo $_REQUEST['auth_key'] ?>" target="_blank">Raise step level</a> |
        <a href="http://zluki.com/break/server/index.php/user/buyItem?item_id=t-shirt_yellow&viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&auth_key=<?php echo $_REQUEST['auth_key'] ?>" target="_blank">Buy item</a> |
        <a href="http://zluki.com/break/server/index.php/user/equipSlot?slot_id=arms&item_id=t-shirt_yellow&viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&auth_key=<?php echo $_REQUEST['auth_key'] ?>" target="_blank">Equip slot</a> |
        <a href="http://zluki.com/break/server/index.php/user/saveSettings?music=1&sfx=0&viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&auth_key=<?php echo $_REQUEST['auth_key'] ?>" target="_blank">Save settings</a> |
        <a href="http://zluki.com/break/server/index.php/battle/test?viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&auth_key=<?php echo $_REQUEST['auth_key'] ?>" target="_blank">Send invite</a> |
        <a href="http://zluki.com/break/server/index.php/xml/load" target="_blank">Parse xmls</a>
    </div>

    <div id="buttons">
        <input type="button" value="Start listening" onclick="pvp.channels.push(new pvp.listener('http://zluki.com/sub?cid=<?php echo $_REQUEST['viewer_id'] ?>', pvp.onSuccess, pvp.onError));" />
        <input type="button" value="Stop listening" onclick="pvp.channels[0].stop()" /><br />
        To: <input type="text" value="<?php echo $_REQUEST['viewer_id'] ?>" name="cid" id="cid" /><br />
        Type: <input type="text" value="" name="type" id="type" /><br />
        Message: <input type="text" value="Hello" name="text" id="text" />
        <input type="button" value="Send hello" onclick="pvp.sendMessage(<?php echo $_REQUEST['viewer_id'] ?>, $('#cid').val(), $('#text').val(), $('#type').val())" />
    </div>

    <div id="messages">
    </div>
</body>
</html>
