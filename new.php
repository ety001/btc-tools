<?php 
if(file_exists('.env')){
    $contents = file_get_contents('.env');
    $api = explode('|', trim($contents));
} else {
    echo 'no api';die();
}
?>
<!DOCTYPE html>
<head>
    <meta charset="utf-8" />
    <title>WebSocket</title>
    <script type="text/javascript" src="jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="MD5.js"></script>
    <script type="text/javascript">  
        var wsUri ="wss://real.okcoin.cn:10440/websocket/okcoinapi"; 
        var output;  
        var lastHeartBeat = new Date().getTime();
        var api_key = '<?php echo $api[0];?>'; //用户申请api_key
        var secret_key = '<?php echo $api[1];?>';//用户申请 secret_key
        var overtime = 8000;
        var last_price = 0;

        var abc = {
            obj : null,
            checkConnect: function() {
                abc.obj.send("{'event':'ping'}");
                if( (new Date().getTime()-lastHeartBeat)>overtime){
                    onsole.log("socket 连接断开，正在尝试重新建立连接");
                    //abc.init();
                }
            },
            init : function () { 
                var websocket = new WebSocket(wsUri);
                abc.obj = websocket;
                websocket.onopen = function(evt) {
                    abc.onOpen(evt);
                }; 
                websocket.onclose = function(evt) {
                    abc.onClose(evt);
                };
                websocket.onmessage = function(evt) {
                    abc.onMessage(evt);
                }; 
                websocket.onerror = function(evt) {
                    abc.onError(evt);
                };
            },
            close : function() {
                abc.obj.close();
            },
            onOpen : function(evt){
                abc.getticker();
                console.log('on_open', evt);
            },
            onClose : function(evt){
                console.log('on_close', evt);
            },
            onMessage : function(evt){
                var data = JSON.parse(evt.data)[0];
                switch(data.channel){
                    case 'ok_btccny_ticker':
                        showinfo.ok_btccny_ticker( data.data );
                        break;
                }
                console.log(data);
            },
            onError : function(evt){
                console.log('on_error', evt);
            },
            getrealtrades : function(){
                var sign = MD5('api_key=<?php echo $api[0];?>&secret_key=<?php echo $api[1];?>');
                var req = {
                    'event':'addChannel',
                    'channel':'ok_cny_realtrades',
                    'parameters':{
                        'api_key':'<?php echo $api[0];?>',
                        'sign':sign
                    }
                };
                var req1 = JSON.stringify(req);
                abc.obj.send(req1);
            },
            getticker : function(){
                abc.obj.send("{'event':'addChannel','channel':'ok_btccny_ticker'}");
            }

        }

        var showinfo = {
            ok_btccny_ticker : function(d){
                $('#buy').html('买价:'+d.buy);
                $('#sell').html('卖价:'+d.sell);
                $('#low').html('最低:'+d.low);
                $('#high').html('最高:'+d.high);
                $('#last').html('成交:'+d.last);
                last_price = d.last;
            }
        };

        $(function(){
            abc.init();
            $('#start').click(function(){
                abc.init();
            });
            $('#stop').click(function(){
                abc.close();
            })
        });
    </script>
    <style type="text/css">
        .status {
            margin-bottom: 10px;
        }
        .status .box {
            width: 96px;
            height: 30px;
            line-height: 30px;
            margin: 0 10px;
            padding: 4px 20px;
            background-color: #ccc;
            float: left;
        }
        .status button {
            display: block;
            width: 96px;
            height: 30px;
            line-height: 14px;
            margin: 0 10px;
            padding: 4px 20px;
            background-color: #ddd;
            float: left;
        }
        #buy {
            background-color: #21DA52 !important;
            color: #fff;
        }
        #sell {
            background-color: #F14646 !important;
            color: #fff;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>
    <div class="status">
        <div class="box" id="low"></div>
        <div class="box" id="high"></div>
        <div class="clear"></div>
    </div>
    <div class="status">
        <div class="box" id="buy"></div>
        <div class="box" id="sell"></div>
        <div class="box" id="last_price"></div>
        <button id="start">Start</button>
        <button id="stop">Stop</button>
        <div class="clear"></div>
    </div>
    <div id="output"></div>
</body>
</html>
