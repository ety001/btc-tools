<?php 
if(file_exists('.env')){
    $contents = file_get_contents('.env');
    $api = explode('|', trim($contents));
} else {
    echo 'no api';die();
}
?>
<!DOCTYPE html>
<meta charset="utf-8" />
<title>WebSocket Test</title>
<script type="text/javascript" src="jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="MD5.js"></script>
<script type="text/javascript">  
    var wsUri ="wss://real.okcoin.cn:10440/websocket/okcoinapi"; 
    var output;  
    var lastHeartBeat = new Date().getTime();
    var api_key = '<?php echo $api[0];?>'; //用户申请api_key
    var secret_key = '<?php echo $api[1];?>';//用户申请 secret_key
    var overtime = 8000;
	
    function init() { 
        output = document.getElementById("output"); 
        testWebSocket();
		//setInterval(checkConnect,5000);
    }  
	
   function checkConnect() {
	websocket.send("{'event':'ping'}"); 
	if( (new Date().getTime()-lastHeartBeat)>overtime){
	    onsole.log("socket 连接断开，正在尝试重新建立连接");
		   //testWebSocket();
	}
    }
	  
    function testWebSocket() { 
        websocket = new WebSocket(wsUri); 
        websocket.onopen = function(evt) { 
            onOpen(evt) 
        }; 
        websocket.onclose = function(evt) {
            onClose(evt) 
        }; 
        websocket.onmessage = function(evt) { 
           onMessage(evt) 
        }; 
        websocket.onerror = function(evt) { 
            onError(evt) 
        }; 
        
    }  
	
    //现货下单
    function spotTrade() {
	var sign = MD5("amount=0.1&api_key="+api_key+"&symbol=ltc_usd&type=sell_market&secret_key="+secret_key);
        doSend("{'event':'addChannel','channel':'ok_spotusd_trade','parameters':{'api_key':'"+api_key+"','sign':'"+sign+"','symbol':'ltc_usd','type':'sell_market','amount':0.1}}");
    }
	
    //现货取消订单
    function spotCancelOrder(orderId) {
	var sign = MD5("api_key="+api_key+"&order_id="+orderId+"&symbol=ltc_usd&secret_key="+secret_key);
	doSend("{'event':'addChannel','channel':'ok_spotusd_cancel_order','parameters':{'api_key':'"+api_key+"','sign':'"+sign+"','symbol':'ltc_usd','order_id':'"+orderId+"'}}");
    }
  
    //期货下单
    function futureTrade() {
	var sign = MD5("amount=1&api_key="+api_key+"&contract_type=this_week&lever_rate=20&match_price=1&price=1.5&symbol=ltc_usd&type=0&secret_key="+secret_key);
	doSend("{'event': 'addChannel','channel':'ok_futuresusd_trade','parameters': {'api_key': '"+api_key+"','sign': '"+sign+"','symbol': 'ltc_usd','contract_type': 'this_week','amount': '1','price': '1.5','type': '0','match_price': '1','lever_rate': '20'}}");
    }
	
    //期货取消订单
    function futureCancelOrder(orderId) {
	var sign = MD5("api_key="+api_key+"&contract_type=this_week&order_id="+orderId+"&symbol=ltc_usd&secret_key="+secret_key);
        doSend("{'event': 'addChannel','channel': 'ok_futuresusd_cancel_order','parameters': {'api_key': '"+api_key+"','sign': '"+sign+"','symbol': 'ltc_usd','order_id': '"+orderId+"','contract_type': 'this_week'}}");
    }
	
    //期货个人信息
    function futureUserInfo(){
	var sign = MD5("api_key="+api_key+"&secret_key="+secret_key);
	doSend("{'event':'addChannel','channel':'ok_futureusd_userinfo','parameters' :{'api_key':'"+api_key+"','sign':'"+sign+"'}}");
    }
	
    //现货个人信息
    function spotUserInfo(){
	var sign = MD5("api_key="+api_key+"&secret_key="+secret_key);
	doSend("{'event':'addChannel','channel':'ok_spotusd_userinfo','parameters' :{'api_key':'"+api_key+"','sign':'"+sign+"'}}");
    }
	
    //现货实时交易
    function ok_usd_realtrades(){
	var sign = MD5("api_key="+api_key+"&secret_key="+secret_key);
	doSend("{'event':'addChannel','channel':'ok_usd_realtrades','parameters':{'api_key':'"+api_key+"','sign':'"+sign+"'}}");
    }
    //期货实时交易
    function ok_usd_future_realtrades(){
	 var sign = MD5("api_key="+api_key+"&secret_key="+secret_key);
	 doSend("{'event':'addChannel','channel':'ok_usd_future_realtrades','parameters':{'api_key':'"+api_key+"','sign':'"+sign+"'}}");
    }
  
    function onOpen(evt) { 
        writeToScreen("CONNECTED"); 
		doSend("{'event':'addChannel','channel':'ok_btccny_trades_v1'}");
    }  
  
    function onClose(evt) { 
        writeToScreen("DISCONNECTED"); 
    }  
  
    function onMessage(evt) { 
        //writeToScreen('<span style="color: blue;">RESPONSE: '+ evt.data+'</span>'); 
        var myList = evt.data;    
        //console.log(myList);
        var myList1 = JSON.parse(myList);
        myList1 = myList1[0];
        var data = myList1.data;
        var len = data.length - 1;
        var last_data_type = (data[len][4]=='bid')?'bid':'ask';
        var last_bid_price = 0;
        var last_ask_price = 0;
        var find_type = '';
        if(last_data_type=='bid'){
            last_bid_price = data[len][1];
            find_type = 'ask';
        } else {
            last_ask_price = data[len][1];
            find_type = 'bid';
        }
        var bid_num = 0;
        var ask_num = 0;
        var no_find = 1;
        for(var i = len; i>=0; i--){
            if(data[i][4]=='bid'){
                bid_num++;
            } else {
                ask_num++;
            }
            if(data[i][4]==find_type&&no_find){
                if(find_type=='bid'){
                    last_bid_price = data[i][1];
                    no_find = 0;
                } else {
                    last_ask_price = data[i][1];
                    no_find = 0;
                }
            }
        }
        console.log('last_bid_price', last_bid_price);
        console.log('last_ask_price', last_ask_price);
        console.log('bid_num', bid_num);
        console.log('ask_num', ask_num);
        console.log('!!', (bid_num<ask_num)?'down':'up');
        console.log(new Date().getTime())

        var array = JSON.parse(myList)
        var result = array.event;
        var isTrade = false;
        var isCancelOrder = false;
	    for (var i = 0; i < array.length; i++) {
		for (var index in array[i]) {
	             var temp=array[i][index];
		     if(temp == 'ok_spotusd_trade' || temp == 'ok_spotcny_trade'){
			   isTrade = true;
		      }else if(temp == 'ok_spotusd_cancel_order' || temp =='ok_spotcny_cancel_order'){
		           isCancelOrder = true;
		      }
		      var order_id = temp.order_id;
		      if(typeof(order_id)!='undefined'){
			       if(isTrade){
				   console.log("orderId is  "+order_id);
			           //下单成功 业务代码
			        }else if(isCancelOrder){
			           console.log("order  "+order_id+" is now cancled");
				   //取消订单成功 业务代码
			        }
		      }
	          }
	     }
	     if(result == 'pong'){
		lastHeartBeat = new Date().getTime();
	     }else{
                createTable(array);
	     }
         			  
    }  
	
  
    function onError(evt) { 
        writeToScreen('<span style="color: red;">ERROR:</span> '+ evt.data); 
    }  
  
    function doSend(message) { 
        writeToScreen("SENT: " + message);  
        websocket.send(message); 
    }  
  
    function writeToScreen(message) { 
        var pre = document.createElement("p"); 
        pre.style.wordWrap = "break-word"; 
        pre.innerHTML = message; 
         
    }  
  
    window.addEventListener("load", init, false);  
 
    function createTable(array){
        var  str='<h2 id="th2">WebSocket Test</h2><table id="tdata" border="1"><tr id="tr1">';
        for (var index in array[0]) {
            str += '<th>' + index + '</th>';
        }
        str += '</tr><tr id="tr2">';
       
    	for (var i = 0; i < array.length; i++) {

        	for (var index in array[i]) {
        	     var temp=array[i][index];
                     str += '<td>';
                     var tem=JSON.stringify(temp);            
                     str += tem;
                     str += '</td>';
                }
                str += '</tr>';
    	}
    	str += '</table>';  
    	removeTable('tdata');
     	document.write(str);
     }
     
     function removeTable(id){
        var tbl = document.getElementById(id);
        if(tbl) tbl.parentNode.removeChild(tbl);
        var tt = document.getElementById('th2');
        if(tt) tt.parentNode.removeChild(tt);
     }
   
</script>
<body>

</body>
<div id="status"></div>
<div id="output"></div>
</html>
