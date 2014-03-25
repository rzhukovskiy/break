<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="../client/css/style.css" type="text/css" media="screen" />
        <script type="text/javascript" src="../client/js/app.js?v=<?php echo time(); ?>"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>

        <script src="https://vk.com/js/api/xd_connection.js?2"  type="text/javascript">
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
            flashvars.event_id = '<?php echo json_encode($_REQUEST['event_id']) ?>';
            flashvars.isLocal = "0";

            var d = new Date();
            flashvars.time = d.getTime();

            var params = {
                base: "../client/",
                menu: "false",
                wmode: "Opaque",
                scale: "noscale",
                allowFullscreen: "true",
                allowNetworking : "all",
                allowScriptAccess: "always",
                bgcolor: "#000000" };
            var attributes = { id: "application", name: "application" };
            swfobject.embedSWF(
                    "../client/Main.swf?"+Math.floor(Math.random()*65535),
                    "altContent", "810", "675", "11.4.0",
                    false,
                    flashvars, params, attributes);

            VK.apiId = <?php echo $vk['app_id'] ?>;

            window.onload = (function() {
                VK.init();
            });

            VK.addCallback('onOrderSuccess', function(order_id) {
                response = { payment : 'success'};
                thisMovie("application").sendFromJS( JSON.stringify(response) );
            });
        </script>

        <!-- start Mixpanel --><script type="text/javascript">(function(e,b){if(!b.__SV){var a,f,i,g;window.mixpanel=b;a=e.createElement("script");a.type="text/javascript";a.async=!0;a.src=("https:"===e.location.protocol?"https:":"http:")+'//cdn.mxpnl.com/libs/mixpanel-2.2.min.js';f=e.getElementsByTagName("script")[0];f.parentNode.insertBefore(a,f);b._i=[];b.init=function(a,e,d){function f(b,h){var a=h.split(".");2==a.length&&(b=b[a[0]],h=a[1]);b[h]=function(){b.push([h].concat(Array.prototype.slice.call(arguments,0)))}}var c=b;"undefined"!==
                typeof d?c=b[d]=[]:d="mixpanel";c.people=c.people||[];c.toString=function(b){var a="mixpanel";"mixpanel"!==d&&(a+="."+d);b||(a+=" (stub)");return a};c.people.toString=function(){return c.toString(1)+".people (stub)"};i="disable track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config people.set people.set_once people.increment people.append people.track_charge people.clear_charges people.delete_user".split(" ");for(g=0;g<i.length;g++)f(c,i[g]);
                b._i.push([a,e,d])};b.__SV=1.2}})(document,window.mixpanel||[]);
            mixpanel.init("c01e7edbfb236dbacf52cc2d7eb162f4");</script><!-- end Mixpanel -->
    </head>

    <body>
    <div id="altContent">
        <a href="https://www.adobe.com/go/getflashplayer">
            <img src="https://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
        </a>
    </div>

    <div class="go-down">
        <div id="vk_like"></div>
        <script type="text/javascript">VK.Widgets.Like("vk_like", {type: "button"});</script>
        <div class="user-id">ID: <?php echo $_REQUEST['viewer_id'] ?></div>
    </div>

    <script type="text/javascript">

        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-5175580-9']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();

    </script>

    <?php if($_REQUEST['viewer_id'] == 1 || $_REQUEST['viewer_id'] == 812177 || $_REQUEST['viewer_id'] == 6489966 || $_REQUEST['viewer_id'] == 5201313) { ?>
    <!-- DEV -->
    <div class="dev">
        <div class="tests">
            <a href="/server/index.php/user/get?viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&amp;auth_key=198a038c272bdab32fa5e0fc9a3314ef" target="_blank">Get user</a> |
            <a href="/server/index.php/user/add?hair_id=1&amp;face_id=1&amp;viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&amp;auth_key=198a038c272bdab32fa5e0fc9a3314ef" target="_blank">Add user</a> |
            <a href="/server/index.php/user/delete?viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&amp;auth_key=198a038c272bdab32fa5e0fc9a3314ef" target="_blank">Delete user</a> |
            <a href="/server/index.php/user/learnStep?energy_spent=100&amp;step_id=indian_step&amp;viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&amp;auth_key=198a038c272bdab32fa5e0fc9a3314ef" target="_blank">Raise step level</a> |
            <a href="/server/index.php/user/buyItem?item_id=jeans_blue&amp;color=blue&amp;viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&amp;auth_key=198a038c272bdab32fa5e0fc9a3314ef" target="_blank">Buy item</a> |
            <a href="/server/index.php/user/sellItem?user_item_id=270&amp;viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&amp;auth_key=198a038c272bdab32fa5e0fc9a3314ef" target="_blank">Sell item</a> |
            <a href="/server/index.php/user/equipSlot?slot_id=arms&amp;user_item_id=13&amp;viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&amp;auth_key=198a038c272bdab32fa5e0fc9a3314ef" target="_blank">Equip slot</a> |
            <a href="/server/index.php/user/saveSettings?music=1&amp;sfx=0&amp;viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&amp;auth_key=198a038c272bdab32fa5e0fc9a3314ef" target="_blank">Save settings</a> |
            <a href="/server/index.php/battle/test?viewer_id=<?php echo $_REQUEST['viewer_id'] ?>&amp;auth_key=198a038c272bdab32fa5e0fc9a3314ef" target="_blank">Send invite</a> |
            <a href="/server/index.php/xml/load" target="_blank">Parse xmls</a> |
            <a href="#" onclick="social.placeOrder(1);return false;">Test payments</a> |
            <a href="#" onclick="social.getAlbums();return false;">Get Albums</a>
        </div>

        <div id="buttons">
            <input value="Start listening" onclick="pvp.channels.push(new pvp.listener('/sub?cid=<?php echo $_REQUEST['viewer_id'] ?>', pvp.onSuccess, pvp.onError));" type="button">
            <input value="Stop listening" onclick="pvp.channels[0].stop()" type="button"><br>
            To: <input value="<?php echo $_REQUEST['viewer_id'] ?>" name="cid" id="cid" type="text"><br>
            Type: <input value="" name="type" id="type" type="text"><br>
            Message: <input value="Hello" name="text" id="text" type="text">
            <input value="Send hello" onclick="pvp.sendMessage(<?php echo $_REQUEST['viewer_id'] ?>, $('#cid').val(), $('#text').val(), $('#type').val())" type="button">
        </div>

        <div id="messages">
        </div>
    </div>
    <!-- /DEV -->
    <?php } ?>
</body>
</html>
