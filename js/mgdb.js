
function MGDatabase(){
	var that = this;
	//
	this.elementList = new Array();
	this.atomList = new Array();
	this.relationList = new Array();
	//
	this.eventHandler = {
		atomAdded: null,				// function(e, db);
		relationAdded: null,			// function(e, db);
		attemptToAddAtom: null,			// function(e, db); : e
		attemptToAddRelation: null,		// function(e, db); : e
	};
}
MGDatabase.prototype = {
	SaveDataPrefix: "mgdbdata",
	//
	// Add
	//
	addAtom: function(contents){
		// retv: elementID
		var e;
		e = new MGDatabaseAtomElement(contents);
		return this.addElement(e);
	},
	addRelation: function(typeID, elementIDList){
		// retv: elementID
		var e;
		e = new MGDatabaseRelationElement(typeID, elementIDList);
		return this.addElement(e);
	},
	addElement: function(e){
		// retv: elementID
		if(e instanceof MGDatabaseAtomElement){
			if(this.eventHandler.attemptToAddAtom){
				e = this.eventHandler.attemptToAddAtom(e, this);
			}
		} else if(e instanceof MGDatabaseRelationElement){
			if(this.eventHandler.attemptToAddRelation){
				e = this.eventHandler.attemptToAddRelation(e, this);
			}
		}
		if(!(e instanceof MGDatabaseElement)){
			console.log("addElement: e is not a MGDatabaseElement.");
			return null;
		}
		if(!UUID.verifyUUID(e.elementID)){
			e.elementID = UUID.generateVersion4();
		}
		if(this.elementList.includes(e.elementID, this.fEqualTo_elementList_elementID)){
			console.log("addElement: elementID already exists.");
			return null;
		}
		
		if(e instanceof MGDatabaseAtomElement){
			this.atomList.push(e);
			if(this.eventHandler.atomAdded){
				this.eventHandler.atomAdded(e, this);
			}
		} else if(e instanceof MGDatabaseRelationElement){
			this.relationList.push(e);
			if(this.eventHandler.relationAdded){
				this.eventHandler.relationAdded(e, this);
			}
		} else{
			console.log("addElement: e is an unknown MGDatabaseElement.");
			return null;
		}
		this.elementList.push(e);
		
		//console.log("Element added.");
		//console.log(e.getStringRepresentation());
		// console.log(mgdb);
		
		return e.elementID;
	},
	addElementFromStringRepresentation: function(str){
		var t;
		if(str.length <= 32 + 4 + 1){
			// UUIDより短い場合は明らかにデータではないので読み込まない
			return;
		}
		if(str.charAt(0) === "#"){
			// Node
			t = new MGDatabaseAtomElement();
		} else if(str.charAt(0) === "$"){
			// Edge
			t = new MGDatabaseRelationElement();
		}
		if(t){
			t.loadStringRepresentation(str);
			this.addElement(t);
		}
	},
	//
	// Search / Get
	//
	getElementByID: function(eid){
		// retv: element instance or false
		if(eid == UUID.nullUUID){
			return false;
		}
		return this.elementList.includes(eid, this.fEqualTo_elementList_elementID);
	},
	getElementByContents: function(contents){
		// retv: element instance or false
		return this.elementList.includes(contents, this.fEqualTo_elementList_contents);
	},
	getListOfRelationConnectedWithElementID: function(eid){
		var a;
		//
		if(eid == UUID.nullUUID){
			return retv;
		}
		//
		retv = this.relationList.getAllMatched(eid, function(r){
			return r.elementIDList.includes(eid);
		});
		return retv;
	},
	//
	// fEqualTo
	//
	fEqualTo_elementList_elementID: function(anElement, elementID){
		return (anElement.elementID === elementID);
	},
	fEqualTo_elementList_contents: function(anElement, contents){
		return (anElement.contents === contents);
	},
	//
	// Load / Save
	//
	loadDBDataFromLocalStorage: function(savename){
		var key = this.SaveDataPrefix;
		if(savename && savename.length > 0){
			key += "_" + savename.trim();
		}
		this.loadDBDataStr(localStorage.getItem(key));
	},
	saveDBDataToLocalStorage: function(savename){
		var dbstr = this.createDBDataStr();
		var key = this.SaveDataPrefix;
		if(savename && savename.length > 0){
			key += "_" + savename.trim();
		}
		localStorage.setItem(key, dbstr);
	},
	createDBDataStr: function(){
		var str = "";
		for(var i = 0, iLen = this.elementList.length; i < iLen; i++){
			str += this.elementList[i].getStringRepresentation();
		}
		return str;
	},
	getURLForDBDataStr: function(){
		var str = this.createDBDataStr();
		//
		var d = new Blob([str]);
		if(d){
			d = this.createURLForBlob(d)
			return d;
		}
		return null;
	},
	loadDBDataStr: function(datastr){
		if(!datastr){
			console.log("[loadDBString] Invalid DBString.\n");
			return false;
		}
		this.resetDB();
		//
		var list = datastr.split("\n");
		for(var i = 0, iLen = list.length; i < iLen; i++){
			this.addElementFromStringRepresentation(list[i]);
		}
		return true;
	},
	createURLForBlob: function(blobData){
		//http://www.atmarkit.co.jp/ait/articles/1112/16/news135_2.html
		//http://qiita.com/mohayonao/items/fa7d33b75a2852d966fc
		if(window.URL){
			return window.URL.createObjectURL(blobData);
		} else if(window.webkitURL){
			return window.webkitURL.createObjectURL(blobData);
		}
		return null;
	},
	//
	// Reset
	//
	resetDB: function(){
		this.elementList = new Array();
		this.atomList = new Array();
		this.relationList = new Array();
	},
}

function MGDatabaseElement(){
	this.elementID = null;
}
MGDatabaseElement.prototype = {
	copyFrom: function(e){
		this.elementID = e.elementID;
	},
}

MGDatabaseAtomElement = function(contents){
	MGDatabaseAtomElement.base.call(this);
	this.contents = contents;
}.extend(MGDatabaseElement, {
	loadStringRepresentation: function(str){
		// retv: elementID
		if(str[0] !== "#"){
			console.log("loadStringRepresentation: str is not valid.");
			return null;
		}
		this.elementID = UUID.verifyUUID(str.substr(1, 32 + 4));
		//
		p = str.lastIndexOf(" ") + 1;
		this.contents = decodeURIComponent(str.substring(p).trim());
		return this.elementID;
	},
	getStringRepresentation: function(){
		// 末尾には改行文字が自動で付加されます。
		var str = "";
		str += "#";
		str += this.elementID;
		str += " ";
		str += encodeURIComponent(this.contents);
		str += "\n";
		return str;
	},
	copyFrom: function(e){
		MGDatabaseAtomElement.base.prototype.copyFrom.call(this, e);
		this.contents = e.contents;
	},
});

MGDatabaseRelationElement = function(typeElementID, elementIDList){
	MGDatabaseRelationElement.base.call(this);
	this.typeElementID = UUID.verifyUUID(typeElementID);
	if(!this.typeElementID){
		this.typeElementID = UUID.nullUUID;
	}
	this.elementIDList = new Array();
	if(elementIDList instanceof Array){
		for(var i = 0; i < elementIDList.length; i++){
			this.elementIDList[i] = UUID.verifyUUID(elementIDList[i]);
			if(!this.elementIDList[i]){
				this.elementIDList[i] = UUID.nullUUID;
			}
		}
	}
}.extend(MGDatabaseElement, {
	loadStringRepresentation: function(str){
		// retv: elementID
		if(str[0] !== "$"){
			console.log("loadStringRepresentation: str is not valid.");
			return null;
		}
		this.elementID = UUID.verifyUUID(str.substr(1, 32 + 4));
		p = 32 + 4;
		//
		p = str.indexOf("#", p) + 1;
		if(p == 0){
			console.log("loadStringRepresentation: str is not valid.");
			return null;
		}
		this.typeElementID = UUID.verifyUUID(str.substr(p, 32 + 4));
		//
		this.elementIDList = new Array();
		for(var i = 0; ; i++){
			p = str.indexOf("#", p) + 1;
			if(p == 0){
				break;
			}
			this.elementIDList[i] = UUID.verifyUUID(str.substr(p, 32 + 4));
		}
		return this.elementID;
	},
	getStringRepresentation: function(){
		// 末尾には改行文字が自動で付加されます。
		var str = "";
		//
		str += "$";
		str += this.elementID;
		//
		str += " #";
		str += this.typeElementID;
		for(var i = 0; i < this.elementIDList.length; i++){
			str += " #";
			str += this.elementIDList[i];
		}
		str += "\n";
		return str;
	},
	copyFrom: function(e){
		MGDatabaseRelationElement.base.prototype.copyFrom.call(this, e);
		this.typeElementID = e.typeElementID;
		this.elementIDList = e.elementIDList.copy();
	},
});

function MGDatabaseQuery(db, domain){
	// domainは省略可能で、指定するならばatom, relation, elementsのいずれかである。
	this.conditionalFunc = null;	// function(objInstance){};
	this.nextIndex = 0;
	this.hasReachedEnd = false;
	this.list = db.elementList;
	switch(domain){
		case "atom":
			this.list = db.atomList;
			break;
		case "relation":
			this.list = db.atomList;
			break;
	}
}
MGDatabaseQuery.prototype = {
	setCondition: function(conditionalFunc){
		this.conditionalFunc = conditionalFunc;
		this.nextIndex = 0;
		this.hasReachedEnd = false;
	},
	getNextMatched: function(){
		if(!this.conditionalFunc){
			return false;
		}
		for(var i = this.nextIndex, iLen = this.list.length; i < iLen; i++){
			if(this.conditionalFunc(this.list[i])){
				this.nextIndex = i + 1;
				return this.list[i];
			}
		}
		this.hasReachedEnd = true;
		return false;
	}
}
