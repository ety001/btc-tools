var wsUri ="wss://real.okcoin.cn:10440/websocket/okcoinapi"; 
var output;  
var lastHeartBeat = new Date().getTime();
var overtime = 8000;
var last_price = 0;
var notification_val = 0;
var notification_val2 = 0;

var abc = {
    obj : null,
    checkConnect: function() {
        abc.obj.send("{'event':'ping'}");
        if( (new Date().getTime()-lastHeartBeat)>overtime){
            console.log("socket 连接断开，正在尝试重新建立连接");
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
        helper.msg('opened');
        console.log('opened');
        //console.log('on_open', evt);
    },
    onClose : function(evt){
        helper.msg('on_close');
        console.log('on_close', evt);
    },
    onMessage : function(evt){
        var data = JSON.parse(evt.data)[0];
        switch(data.channel){
            case 'ok_btccny_ticker':
                info.ok_btccny_ticker( data.data );
                abc.getuserinfo();
                break;
            case 'ok_spotcny_trade':
                if(data.errorcode == undefined){
                    info.ok_spotcny_trade( data.data );
                } else {
                    helper.msg('errorcode:'+data.errorcode);
                    console.log('errorcode:'+data.errorcode);
                }
                break;
            case 'ok_spotcny_userinfo':
                info.ok_spotcny_userinfo(data.data);
                break;
        }
        //console.log(data);
    },
    onError : function(evt){
        console.log('on_error', evt);
    },
    getuserinfo : function(){
        var sign = MD5('api_key='+api_key+'&secret_key='+secret_key);
        var req = {
            'event':'addChannel',
            'channel':'ok_spotcny_userinfo',
            'parameters':{
                'api_key':api_key,
                'sign':sign
            }
        };
        var req1 = JSON.stringify(req);
        abc.obj.send(req1);
    },
    getrealtrades : function(){
        var sign = MD5('api_key='+api_key+'&secret_key='+secret_key);
        var req = {
            'event':'addChannel',
            'channel':'ok_cny_realtrades',
            'parameters':{
                'api_key':api_key,
                'sign':sign
            }
        };
        var req1 = JSON.stringify(req);
        abc.obj.send(req1);
    },
    getticker : function(){
        abc.obj.send("{'event':'addChannel','channel':'ok_btccny_ticker'}");
    },
    buy : function(amount){
        var sign = MD5('amount='+amount+'&api_key='+api_key+'&price='+last_price+'&symbol=btc_cny&type=buy&secret_key='+secret_key);
        var req = {
            'event':'addChannel',
            'channel':'ok_spotcny_trade',
            'parameters':{
               'api_key':api_key,
               'sign':sign,
               'symbol':'btc_cny',
               'type':'buy',
               'price':last_price,
               'amount':amount
            }
        };
        var req1 = JSON.stringify(req);
        abc.obj.send(req1);
        var msg = 'buy '+amount+' btc on price '+last_price;
        helper.msg(msg);
        console.log(msg);
    },
    sell : function(amount){
        var sign = MD5('amount='+amount+'&api_key='+api_key+'&price='+last_price+'&symbol=btc_cny&type=sell&secret_key='+secret_key);
        var req = {
            'event':'addChannel',
            'channel':'ok_spotcny_trade',
            'parameters':{
               'api_key':api_key,
               'sign':sign,
               'symbol':'btc_cny',
               'type':'sell',
               'price':last_price,
               'amount':amount
            }
        };
        var req1 = JSON.stringify(req);
        abc.obj.send(req1);
        var msg = 'sell '+amount+' btc on price '+last_price;
        helper.msg(msg);
        console.log(msg);
    }
}

var info = {
    ok_btccny_ticker : function(d){
        $('#buy').html('买价:'+d.buy);
        $('#sell').html('卖价:'+d.sell);
        //$('#low').html('最低:'+d.low);
        //$('#high').html('最高:'+d.high);
        $('#last').html('成交:'+d.last);
        last_price = d.last;
        if(notification_val>0 && last_price>notification_val){
            var notification = new Notification('okcoin',{body:">"+notification_val});
            notification_val = 0;
        }
        if(notification_val2>0 && last_price<notification_val2){
            var notification = new Notification('okcoin',{body:"<"+notification_val2});
            notification_val2 = 0;
        }
    },
    ok_spotcny_trade : function(d){
        helper.msg(JSON.stringify(d));
        console.log('ok_spotcny_trade:', d);
    },
    ok_spotcny_userinfo : function(d){
        if(d){
            var funds = d.info.funds;
            $('#free_cny').html('CNY : ' + funds.free.cny);
            $('#free_btc').html('BTC : ' + funds.free.btc);
            $('#freezed_cny').html('FCNY : ' + funds.freezed.cny);
            $('#freezed_btc').html('FBTC : ' + funds.freezed.btc);
            $('#asset_net').html('NET : ' + funds.asset.net);
            $('#asset_total').html('TOTAL : ' + funds.asset.total);
        }
    }
};

var helper = {
    msg : function(msg){
        var obj = $('<div class="error" id="m'+new Date().getTime()+'">'+msg+'</div>');
        //$('.msg').append(obj);
        $('.msg').before(obj);
        setTimeout(function(){
            obj.fadeOut('fast').remove();
        },2000);
    },
    set_notifications : function(val){
        notification_val = val;
        if (window.Notification){
            console.log('set alert');
            helper.msg('Set Alert');
            if(Notification.Permission==='granted'){
                
            }else {
                Notification.requestPermission();
            };
        }else alert('你的浏览器不支持此特性，请下载谷歌浏览器试用该功能');
    },
    set_notifications2 : function(val){
        notification_val2 = val;
        if (window.Notification){
            console.log('set alert down');
            helper.msg('Set Alert');
            if(Notification.Permission==='granted'){
                
            }else {
                Notification.requestPermission();
            };
        }else alert('你的浏览器不支持此特性，请下载谷歌浏览器试用该功能');
    }
}

$(function(){
    abc.init();
    $('#start').click(function(){
        abc.init();
    });
    $('#stop').click(function(){
        abc.close();
    });
    $('.buy').click(function(){
        var amount = $(this).next().val();
        abc.buy(amount);
    });
    $('.sell').click(function(){
        var amount = $(this).prev().val();
        abc.sell(amount);
    });
    $('#alert').click(function(){
        if($(this).prev().val()==0)return;
        helper.set_notifications($(this).prev().val());
    });
    $('#alert2').click(function(){
        if($(this).prev().val()==0)return;
        helper.set_notifications2($(this).prev().val());
    });
});