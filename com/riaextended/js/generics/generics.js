/* generic view
================================================== */
function GenericView(){
    
    //ID    
    var ID = Utils.guid();
    
    //get the ID
    this.getID = function(){
        return ID;
    }    
    
    //dom element
    this.el;
    
    //model
    this.model;
    
    //init    
    this.init = function(element, modelM){
        this.el = element;
        this.model = modelM;
        //click handler
        var scope = this;        
        this.el.click(function(e){
            scope.onClick(e);
        });
        
        //render handler
        if(this.model!=undefined){          
            this.model.onChange = this.render;
        }
    }
    
    this.getEl = function(){
        return this.el;
    }
    
    //get model
    this.getModel = function(){
        return this.model;
    }
    
    //render model changes
    this.render = function(){}
    
    //basic validation
    this.validate = function(val){
        var valid = false;
        if(val!=""){
            valid = true;
        }
        return valid;
    }
    
    //to be overriden
    this.onClick = function(e){}    
    
}



/* generic model
================================================== */
function GenericModel(){
    //ID    
    var ID = Utils.guid();        
    
    //set property
    this.setProperty = function(key, value){
        //value here
        this.onChange();
    }        
    //get property
    this.getProperty = function(key){
        
    }
    
    //get the ID
    this.getID = function(){
        return ID;
    }
    
    //change occured
    this.onChange = function(){}
    
    
}
