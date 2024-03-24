<style>
    div#aphp_trace{position:fixed;bottom:0;right:0;font-size:14px;width:100%;z-index:999999;text-align:left}
    div#aphp_trace_tab{display:none;background:white;margin:0;height:250px}
    div#aphp_trace_title{background-color:#f0f1f3;overflow:hidden;height:35px;line-height:35px;padding:0 12px;border-bottom:1px solid #ccc;border-top:1px solid #ccc;font-size:16px}
    div#aphp_trace_title span{color:#000;padding-right:12px;height:35px;line-height:35px;display:inline-block;margin-right:3px;cursor:pointer;font-weight:700}
    div#aphp_trace_content{overflow:auto;height:212px;padding:0;line-height:25px}
    div#aphp_trace_content ul{padding:0;margin:0}
    div#aphp_trace_content ul li{border-bottom:1px solid #ddd;font-size:14px;padding:0 12px}
    div#aphp_trace_content ul li pre{line-height:15px;margin:5px 0}
    div#aphp_trace_close{display:none;text-align:right;height:18px;position:absolute;top:10px;right:12px;cursor:pointer}
    div#aphp_trace_close img{height:18px;vertical-align:top}
    div#aphp_trace_open{height:30px;z-index:9999;float:right;text-align:right;overflow:hidden;position:fixed;bottom:0;right:0;line-height:30px;cursor:pointer}
    div#aphp_trace_open .runtime{background:#232323;color:#FFF;padding:0 6px;float:right;line-height:30px;font-size:14px}
</style>
<div id="aphp_trace">
    <div id="aphp_trace_tab">
        <div id="aphp_trace_title">
            <?php foreach ($tabs as $title):?>
            <span><?php echo $title?></span>
            <?php endforeach?>
        </div>
        <div id="aphp_trace_content">
            <?php foreach ($trace as $name => $items):?>
            <div style="display:none;">
                <ul>
                    <?php
                    foreach ($items as $key => $val) {
                        echo '<li>';
                        if (!is_numeric($key)) {
                            echo $key.'：';
                        } elseif ($name != 'base' && $name != 'debug') {
                            echo ++$key.'. ';
                        }
                        echo $val.'</li>';
                    }
                    ?>
                </ul>
            </div>
            <?php endforeach?>
        </div>
    </div>
    <div id="aphp_trace_close">
        <img alt="close"
             src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAABOUlEQVRYR+2W0W0CMRBE33VACXQAdAAlpAJIBaGTKBUk6SAdJB0AHaQESogG3UknY+Pd9cfl4+7Tsj3v1rOr6Zj46ybWZwb4txVYAC/AJ/Db6JMlsAfegGt6V6kCB+C9P7ADzkGINfAN6IeegQ8rgA78AKsGiLH4Bdh6KiDQFgiTuERqJoxAmMUtAN5KuMStAFYIt7gHoAYREvcClCC0PrRa0e2lNq6ZMHcuNeYA5haPVGAAGkNoLSTeAjB+c92jERuamJEnSA0ngPDE9ALk3C6A8Nj2ADxqtcjEvPnJCmDp8xCEBcAinusOkzFrAB7xEMQjgIi4G6IEoBh16jNBdMikntjk4l0J4Ai8tky4vhRjiCfgyxrJtE8RSlnwLkg686EglDGVB82h1KkR317rgvjNxpMzwOQV+AM8QnIhRC5g4gAAAABJRU5ErkJggg=="/>
    </div>
</div>
<div id="aphp_trace_open">
    <div class="runtime"><?php echo $runtime?> <span style="color:red"><?php echo $errors?></span></div>
    <img width="30" title="APHP DebugBar" alt="logo" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAK+SURBVHja7JfLb01RFMZ36962CUobKWmkDCgREk2E1KMoEY+SEG1TxEBiQGOACQYMSP8AiTQxKqIxoEYiEuJREgYeNRE6aBh4dYSiWnp8S76TrLuyj/Yc162BL/nl3rPP7t1f115r7XPy6jrqnEc14BIY4nUKHAdjwBHw3f258kBvKuJmIZhkxoppoNRlTykx0AImgIvgJm8MeSb/cNnXoBg4zItXysCgZ3I6wthI9Bw8AMvBtIwQgE6GW6KwFARgLugCfWreMzAjweIvwDLZby5+B1RoA7VMiFaaEd0Fm0A1/2u5/xUsTGDgPhcXvQQPrYEwo3Vmf+ZiF7KwzyvATNAN5oMldgv0Hv+NpKtgRJ+CBWCiNXCNOXAZrAJjwWtQmcVsLwOrvXUI1vD7abAYbOGel7kcKCxDqYDb7HZVLocSA9+YdNvU4ifBY898KdGDYCo4EGMdKd89vi4adsIiMy5Noz2if7cwTyRiPTFMdICrdmvzwUfPZNmWcZ5xyY9yUAD2x4z2I7Ce0c4w4FMlDx+rrep7I3PHRmgfaFaUq/vShM5E9YGMQ8LT90vAWvCEod/Mur5hcqTRNJvtbEYDvG4De4eLgE+1bCJHQT3o55jVF3NdTaPapEtiYAcb1HV2yU6O2Q5qc6fLVFQwki2wKmX4W3koic6Cc2AeEyzUKWZ7qHb1N6KmJAZqWKr31NgVftYbA+d/8zuzwe4kBhbxs5nJKOU0mWMbwTGVZFGq4nlTHNdAITN+gE80K9U9qZQ5fIAJo1DEZ0fReJrfAHbyt1xcA+J8Fthl9tZx/BZoUAbaaDQ0XzLcWeAiev4Htcf9/OHAzHvL5rIOHFIJOyXOYeRTAZ//+1hqLuJ9IGD3k855AnwC02O9HODF5F2uzn6P3ue7UdZ/A/+EgfQorp+WMnzD8gpyvPiv1/OfAgwAYfOIea3qbv8AAAAASUVORK5CYII=">
</div>
<script type="text/javascript">
    (function () {
        let tab_tit = document.getElementById('aphp_trace_title').getElementsByTagName('span');
        let tab_cont = document.getElementById('aphp_trace_content').getElementsByTagName('div');
        let open = document.getElementById('aphp_trace_open');
        let close = document.getElementById('aphp_trace_close').children[0];
        let trace = document.getElementById('aphp_trace_tab');
        let cookie = document.cookie.match(/aphp_show_page_trace=(\d\|\d)/);
        let history = (cookie && typeof cookie[1] != 'undefined' && cookie[1].split('|')) || [0, 0];
        open.onclick = function () {
            trace.style.display = 'block';
            this.style.display = 'none';
            close.parentNode.style.display = 'block';
            history[0] = 1;
            document.cookie = 'aphp_show_page_trace=' + history.join('|')
        }
        close.onclick = function () {
            trace.style.display = 'none';
            this.parentNode.style.display = 'none';
            open.style.display = 'block';
            history[0] = 0;
            document.cookie = 'aphp_show_page_trace=' + history.join('|')
        }
        for (let i = 0; i < tab_tit.length; i++) {
            tab_tit[i].onclick = (function (i) {
                return function () {
                    for (let j = 0; j < tab_cont.length; j++) {
                        tab_cont[j].style.display = 'none';
                        tab_tit[j].style.color = '#999';
                    }
                    tab_cont[i].style.display = 'block';
                    tab_tit[i].style.color = '#000';
                    history[1] = i;
                    document.cookie = 'aphp_show_page_trace=' + history.join('|')
                }
            })(i)
        }
        parseInt(history[0]) && open.click();
        tab_tit[history[1]].click();
    })();
</script>