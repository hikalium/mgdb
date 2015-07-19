function NetworkManager(){

}
NetworkManager.prototype = {
	//from PCD2013GSCL
	//https://sourceforge.jp/projects/h58pcdgame/scm/git/GameScriptCoreLibrary/blobs/master/www/corelib/coresubc.js
	//http://hakuhin.jp/js/xmlhttprequest.html
	CreateRequestObject: function(){
		var rq = null;
		// XMLHttpRequest
		try{
			// XMLHttpRequest オブジェクトを作成
			rq = new XMLHttpRequest();
		}catch(e){}
		// Internet Explorer
		try{
			rq = new ActiveXObject('MSXML2.XMLHTTP.6.0');
		}catch(e){}
		try{
			rq = new ActiveXObject('MSXML2.XMLHTTP.3.0');
		}catch(e){}
		try{
			rq = new ActiveXObject('MSXML2.XMLHTTP');
		}catch(e){}
		if(rq == null){
			return null;
		}
		return rq;
	},
	RequestObjectDisableCache: function(rq){
		//call after open request.
		//disable cache
		//http://vird2002.s8.xrea.com/javascript/XMLHttpRequest.html
		rq.setRequestHeader('Pragma', 'no-cache');				// HTTP/1.0 における汎用のヘッダフィールド
		rq.setRequestHeader('Cache-Control', 'no-cache');		// HTTP/1.1 におけるキャッシュ制御のヘッダフィールド
		rq.setRequestHeader('If-Modified-Since', 'Thu, 01 Jun 1970 00:00:00 GMT');
	},
	sendRequestAsync: function(mode, url, data, callback){
		//非同期モード
		//callback(statusCode, statusText, res);
		var q = this.CreateRequestObject();
		var that = this;
		q.onreadystatechange = function(){
			if(q.readyState == 4){
				callback(q.status, q.statusText, q.responseText);
			}
		};
		q.open(mode, url, true);
		//
		data = this.getURIEncodedStringForObject(data);
		q.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		this.RequestObjectDisableCache(q);
		q.send(data);
	},
	getURIEncodedStringForObject: function(obj){
		// 一次元配列まで対応
		var str = "";
		for(key in obj){
			if(str !== ""){
				str += "&";
			}
			if(obj[key] instanceof Array){
				for(var i = 0; i < obj[key].length; i++){
					if(i != 0){
						str += "&";
					}
					str += encodeURIComponent(key) + "[" + i + "]=" + encodeURIComponent(obj[key][i]);
				}
			} else{
				str += encodeURIComponent(key) + "=" + encodeURIComponent(obj[key]);
			}
		}
		return str;
	},
}