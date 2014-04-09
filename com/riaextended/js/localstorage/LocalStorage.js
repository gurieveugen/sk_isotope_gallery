function LocalStorage(){
    var data = new Array();
    
    this.add = function(key, val){
        var valid = this.validateEntry(key);
        if(valid){
            data.push({key: val, ID: key});
        }
        return valid;
    }
    
    this.remove = function(key){
        //TBD
    }
    
    this.getObj = function(key){
        var obj;
        for(var i=0;i<data.length;i++){
            if(data[i].ID==key){
                obj = data[i].val;
            }
        }
        return obj;        
    }
    
    
    this.validateEntry = function(key){
        var valid = true;
        for(var i=0;i<data.length;i++){
            if(data[i].ID==key){
                valid = false;
            }
        }
        return valid;
    }
}
LocalStorage.getInstance=function(){
    if(LocalStorage.instance==null){
        LocalStorage.instance = new LocalStorage();
    }
    return LocalStorage.instance;
}
