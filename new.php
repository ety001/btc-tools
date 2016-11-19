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
    <link rel="stylesheet" type="text/css" href="style.css">
    <script type="text/javascript">
    var api_key = '<?php echo $api[0];?>'; //用户申请api_key
    var secret_key = '<?php echo $api[1];?>';//用户申请 secret_key
    </script>
    <script type="text/javascript" src="jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="MD5.js"></script>
    <script type="text/javascript" src="core.js"></script>
</head>
<body>
    <div class="status">
        <button id="start">Connect</button>
        <button id="stop">Close</button>
    </div>
    <div class="status">
        <div class="small_box" id="free_cny"></div>
        <div class="small_box" id="free_btc"></div>
    </div>
    <div class="status">
        <div class="small_box" id="freezed_cny"></div>
        <div class="small_box" id="freezed_btc"></div>
    </div>
    <div class="status">
        <div class="small_box" id="asset_net"></div>
        <div class="small_box" id="asset_total"></div>
    </div>
    <div class="status">
        <div class="box" id="buy"></div>
        <div class="box" id="sell"></div>
        <div class="box" id="last"></div>
    </div>
    <div class="status">
        <button class="buy">Buy</button>
        <input type="text" class="amount" value="0.1" />
        <button class="sell">Sell</button>
    </div>
    <div class="status">
        <button class="buy">Buy</button>
        <input type="text" class="amount" value="0.01" />
        <button class="sell">Sell</button>
    </div>
    <div class="status">
        <button class="buy">Buy</button>
        <input type="text" class="amount" value="0.02" />
        <button class="sell">Sell</button>
    </div>
    <div class="status">
        <input type="text" value="0" />
        <button id="alert" class="alert">Alert Up</button>
    </div>
    <div class="status">
        <input type="text" value="0" />
        <button id="alert2" class="alert">Alert Down</button>
    </div>
    <div class="status msg">
    </div>
</body>
</html>
