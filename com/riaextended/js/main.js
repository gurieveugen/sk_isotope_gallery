jQuery(document).ready(function(){
    DEFAULT_IMAGE_HEIGHT = 250;
    DEFAULT_IMAGE_WIDTH = 250;
    igallery_admin = new SkIgalleryAdmin();
    igallery_admin.init();
});

function SkIgalleryAdmin(){
                
   
   
    /* groups view
    ================================================== */   
   function GroupsView(dom_el, model){
       var _this = this;
       this.groupsUI;
       
       this.render = function(){
           //console.log('render');
       }
       
       this.init(dom_el, model);
       
       this.addNewGroupUI = function(e){
           //e.data
           if(_this.groupsUI==undefined){
               _this.groupsUI = new Array();
           }
           //_this.groupsUI           
           var tmpl = jQuery(iGalTemplates.getInstance().getGroupTemplate(e.data));
           //var state = jQuery("#accordion").accordion( "option", "active" );
           jQuery( "#accordion").accordion("destroy");
           tmpl.appendTo(jQuery( "#accordion"));
           initAccordion();
           //jQuery("#accordion").accordion( "option", "active", state );           
           tmpl.attr('data-groupID', e.data.getIdGroup());
           var groupView = new GroupView(tmpl, e.data);
           //console.log(e.data.getIdGroup());
           _this.groupsUI.push(groupView);
       }
              
       
       this.galleryDataChange = function(){
           document.getElementById('mainJSONData').value = JSON.stringify(main_json_gallery_data);
       }
       
       //remove group UI
       this.removeGroup = function(e){
           for(var i=0;i<_this.groupsUI.length;i++){
               if(_.isEqual(_this.groupsUI[i].getModel(), e.data)){
                   _this.groupsUI[i].el.remove();
                   break;
               }
           }
       }
       
       //search for group, add new image to group
       this.addNewImageUI = function(e){           
           var model = e.data.model;
           var groupID = e.data.groupID;
           for(var i=0;i<_this.groupsUI.length;i++){
               if(_this.groupsUI[i].getModel().getIdGroup() == groupID){
                   _this.groupsUI[i].addImageItemUI(model);                   
                   break;
               }
           }           
       }       
       
       EventBus.addEventListener(Event.GROUP_ADDED, this.addNewGroupUI);
       EventBus.addEventListener(Event.GALLERY_DATA_CHANGE, this.galleryDataChange);
       EventBus.addEventListener(Event.REMOVE_GROUP, this.removeGroup);       
       
       EventBus.addEventListener(Event.ADD_NEW_IMAGE, this.addNewImageUI);       
   }
   GroupsView.prototype = new GenericView();
   
    /* groups model
    ================================================== */
   function GroupsModel(){
       var _this = this;
       
       this.setData = function(data){       
           for(var i=0;i<data.length;i++){
               var groupModel = jQuery.extend(new GroupModel(), data[i]);
               _this.addGroup(groupModel);
               
               //ImageItemModel
               if(groupModel.imageItems!=undefined){
                   for(var j=0;j<groupModel.imageItems.length;j++){
                       groupModel.imageItems[j] = jQuery.extend(new ImageItemModel(), groupModel.imageItems[j]);
                       groupModel.unpackImageItem({model: groupModel.imageItems[j], groupID: groupModel.getIdGroup()});                     
                   }                   
               
               }                                   
           }
       }
       
       this.groups;
       this.addGroup = function(groupModel){           
           if(this.groups==undefined){
               this.groups = new Array();
           }
           this.groups.push(groupModel);
           EventBus.dispatchEvent(new Event(Event.GROUP_ADDED, groupModel));
           main_json_gallery_data.setGroups(this.groups);
           EventBus.dispatchEvent(new Event(Event.GALLERY_DATA_CHANGE));                      
       }
       
       this.getGroups = function(){
           return this.groups;
       }       
       this.getGroup = function(groupModel){                      
           for(var i=0;i<this.groups.length;i++){
               if(_.isEqual(this.groups[i], groupModel)){
                   break;
               }
           }
       }
       this.getGroupByID = function(id){
           var gModel;
           for(var i=0;i<_this.groups.length;i++){
               if(_this.groups[i].idGroup==id){
                   gModel = _this.groups[i];
                   break;
               }
           }
           return gModel;           
       }       
       
       this.addNewGroup = function(e){
           var groupM = new GroupModel();
           groupM.setName('Gallery group');
           groupM.setIdGroup(Utils.guid());
           _this.addGroup(groupM);
       }
       
       this.removeGroup = function(e){
            for(var i=0;i<_this.groups.length;i++){
                if(_.isEqual(_this.groups[i], e.data)){
                    _this.groups.splice(i,1);                   
                    main_json_gallery_data.setGroups(_this.groups);
                    EventBus.dispatchEvent(new Event(Event.GALLERY_DATA_CHANGE));                
                    break;
                }
            }           
       }
       
       this.reorderGroups = function(e){
           var newList = new Array();
           var list = e.data;
           for(var i=0;i<list.length;i++){
               var idGroup = list[i]; 
               var gModel = _this.getGroupByID(idGroup);
               newList.push(gModel);
           }
           _this.groups = newList;
           main_json_gallery_data.setGroups(_this.groups);
           EventBus.dispatchEvent(new Event(Event.GALLERY_DATA_CHANGE));
       }
       
       this.updateDATA = function(){
           main_json_gallery_data.setGroups(_this.groups);
           EventBus.dispatchEvent(new Event(Event.GALLERY_DATA_CHANGE));
       }       
       
       EventBus.addEventListener(Event.ADD_NEW_GROUP, this.addNewGroup);
       EventBus.addEventListener(Event.REORDER_GALLERY_GROUPS, this.reorderGroups);
       EventBus.addEventListener(Event.GROUP_CHANGE, this.updateDATA);
       EventBus.addEventListener(Event.REMOVE_GROUP, this.removeGroup);
                     
   }
   GroupsModel.prototype = new GenericModel();      
   
   
    /* group view
    ================================================== */  
    function GroupView(dom_el, model){
        var _this = this;
        this.render = function(){
            //console.log(_this.getModel().getName());
            _this.el.find('.groupHeader').html(_this.getModel().getName());
            EventBus.dispatchEvent(new Event(Event.GROUP_CHANGE));
        }
        this.init(dom_el, model);
        
        var inputName = dom_el.find('.inputText');
        inputName.keyup(function(){
            var m = _this.getModel();
            m.setName(jQuery(this).val());
        });
        
        //remove this group
        var removeBTN = dom_el.find('.removeGroupBTN');
        removeBTN.click(function(e){
            e.preventDefault();
            if(confirm('Are you sure you want to remove this group?')){
                EventBus.dispatchEvent(new Event(Event.REMOVE_GROUP, _this.getModel()));                
            }
        });               
                
        _this.el.find('.itemsContainer').sortable({
            placeholder: "ui-state-highlight",
            stop: function( event, ui ){
                reorderImageItems();        
            }
        });
        _this.el.find('.itemsContainer').disableSelection();
        
        //reorder images 
        function reorderImageItems(){
            var list = new Array();
            var children = _this.el.find('.itemsContainer').children();
            children.each(function(indx){
                list.push(jQuery(this).attr('data-id'));
            });
            _this.getModel().reorderImages(list);                        
        }
        
        this.imagesUIAC;
        //add image iteme view
        this.addImageItemUI = function(imageModel){
             var imItemEl = jQuery('<li data-id="'+imageModel.getIdItem()+'" class="ui-state-default"><div class="imageItemHolder"><img class="adminImageThumb" /><div class="overlayImageItem"><div class="moveIcon"></div><div class="trashIcon"></div><div class="editIcon"></div></div></div></li>');
             var imageView = new ImageItemView(imItemEl, imageModel);
             imItemEl.appendTo(_this.el.find('.itemsContainer'));
             _this.el.find('.itemsContainer').sortable( "refresh" );
             
             var imageItemOverlay = imItemEl.find('.overlayImageItem');
             TweenLite.to(imageItemOverlay, 0, {css:{opacity:0}});
             //TweenLite.to(imItemEl, 1, {css:{opacity:1}, ease:Power3.EaseIn});
             imageItemOverlay.hover(function(e){
                 TweenLite.to(jQuery(this), .3, {css:{opacity:1}, ease:Power3.EaseIn});
             }, function(e){
                 TweenLite.to(jQuery(this), .3, {css:{opacity:0}, ease:Power3.EaseIn});
             });
             
             var removeItemBTN = imItemEl.find('.trashIcon');
             removeItemBTN.click(function(e){
                 e.preventDefault();
                    if(confirm('Are you sure you want to remove this image?')){
                        //EventBus.dispatchEvent(new Event(Event.REMOVE_GROUP, _this.getModel()));
                        _this.getModel().removeImageItem(imageModel.getIdItem());
                        imItemEl.remove();             
                    }                 
             });
             
             var editCaptionBTN = imItemEl.find('.editIcon');
             editCaptionBTN.click(function(e){
                 e.preventDefault();
                 new EditCaption(imageModel);                
             });             
        }
        
        //add images button
        var addImagesBTN = dom_el.find('.addImagesBTN');
        addImagesBTN.click(function(e){
              e.preventDefault();
              var send_attachment_bkp = wp.media.editor.send.attachment;
                    var frame = wp.media({
                        title: "Select Image",
                        multiple: true,
                        library: { type: 'image' },
                        button : { text : 'add image' }
                    });
                    
                    frame.on('close',function() {                        
                        var selection = frame.state().get('selection');
                        selection.each(function(attachment) {
                               var imItemM = new ImageItemModel();
                               imItemM.setImageHeight(DEFAULT_IMAGE_HEIGHT);
                               imItemM.setImageWidth(DEFAULT_IMAGE_WIDTH);
                               imItemM.setIdItem(Utils.guid());
                               imItemM.setAttachementID(attachment.id);                               
                               imItemM.setCaption(attachment.attributes.title);
                               _this.getModel().addImageItem({model: imItemM, groupID: _this.getModel().getIdGroup()});                                                   
                        });
                         wp.media.editor.send.attachment = send_attachment_bkp;
                    });                                  
                            
                    frame.open();
             return false;                          
        });
    }
    GroupView.prototype = new GenericView();
       
    /* group model
    ================================================== */   
   function GroupModel(){
       var _this = this;
       this.name;
       this.setName = function(val){
           this.name = val;
           this.onChange();
       }
       this.getName = function(){
           return this.name;
       }
       
       this.idGroup;
       this.setIdGroup = function(val){
           this.idGroup = val;
       }
       this.getIdGroup = function(){
           return this.idGroup;
       }
       
       this.imageItems;             
       this.unpackImageItem = function(data){
           EventBus.dispatchEvent(new Event(Event.ADD_NEW_IMAGE, {model: data.model, groupID: data.groupID}));
       }
       
       this.addImageItem = function(data){
           if(this.imageItems==undefined){
               this.imageItems = new Array();
           }
           this.imageItems.push(data.model);
           EventBus.dispatchEvent(new Event(Event.ADD_NEW_IMAGE, {model: data.model, groupID: data.groupID}));
           EventBus.dispatchEvent(new Event(Event.GROUP_CHANGE));
       }
       
       //remove image item
       this.removeImageItem = function(itemID){
           for(var i=0;i<_this.imageItems.length;i++){               
               if(_this.imageItems[i].getIdItem()==itemID){
                   //remove item
                   _this.imageItems.splice(i,1);
                   EventBus.dispatchEvent(new Event(Event.GROUP_CHANGE));
                   break;
               }
           }
       }
       /*
       this.removeGroup = function(e){
            for(var i=0;i<_this.groups.length;i++){
                if(_.isEqual(_this.groups[i], e.data)){
                    _this.groups.splice(i,1);                   
                    main_json_gallery_data.setGroups(_this.groups);
                    EventBus.dispatchEvent(new Event(Event.GALLERY_DATA_CHANGE));                
                    break;
                }
            }           
       }        
        */
       
       //reorder image items mdels
       this.reorderImages = function(imagesList){
           var newList = new Array();
           var list = imagesList;
           for(var i=0;i<list.length;i++){
               var idImageItem = list[i]; 
               var iModel = _this.getImageItemByID(idImageItem);
               newList.push(iModel);
           }
           _this.imageItems = newList;           
           EventBus.dispatchEvent(new Event(Event.GROUP_CHANGE));          
       } 
              
       this.getImageItemByID = function(id){
           var iModel;
           for(var i=0;i<_this.imageItems.length;i++){
               if(_this.imageItems[i].itemID==id){
                   iModel = _this.imageItems[i];
                   break;
               }
           }
           return iModel;           
       }             
   }
   GroupModel.prototype = new GenericModel();
   
   
   
    /* image item view
    ================================================== */
    function ImageItemView(dom_el, model){
        var _this = this;
        this.render = function(){
            
        }
        this.init(dom_el, model);
        
        
        getImage(model.getAttachementID());
        function getImage(id){
            jQuery.post(
                IGALLERY_DTA.AJAX_SERVICE,
                {
                    action : 'igallery_ajax_req',
                    attachementID : id,
                    i_nonce: IGALLERY_DTA.IGALLERY_NONCE
                },
                function( response ) {
                    _this.el.find('.adminImageThumb').attr('src', response.thumb_url);
                    //console.log( response.thumb_url );
                }
            );            
        }        
    }
    ImageItemView.prototype = new GenericView();   
             
   
    /* image item model
    ================================================== */    
   function ImageItemModel(){
       this.attachementID;
       this.setAttachementID = function(val){
           this.attachementID = val;
           this.onChange();
       }
       this.getAttachementID = function(){
           return this.attachementID;
       }
                     
       this.itemID;
       this.setIdItem = function(val){
           this.itemID = val;
       }       
       this.getIdItem = function(){
           return this.itemID;
       }
              
       this.caption;
       this.setCaption = function(val){
           this.caption = val;
           this.onChange();
       }
       this.getCaption = function(val){
           return this.caption;
       }
       
       this.subcaption;
       this.setSubCaption = function(val){
           this.subcaption = val;
           this.onChange();
       }
       this.getSubCaption = function(val){
           return this.subcaption;
       }

       this.imageHeight;
       this.setImageHeight = function(val){
           this.imageHeight = val;
       }
       this.getImageHeight = function(){
           return this.imageHeight;
       }

       this.imageWidth;
       this.setImageWidth = function(val){
           this.imageWidth = val;
       }
       this.getImageWidth = function(){
           return this.imageWidth;
       }

       this.imageURL;
       this.setImageURL = function(val){
          if(typeof(val) != 'undefined')
          {
            this.imageURL = val; 
          }
           
       }
       this.getImageURL = function(){
          if(typeof(this.imageURL) != 'undefined')
          {
            return this.imageURL; 
          }
          return '';           
       }
   }
   ImageItemModel.prototype = new GenericModel();
   
   
   
   
   
    var main_json_gallery_data;
    /* init
    ================================================== */    
    this.init = function(){
        var json= document.getElementById('mainJSONData').value;        
        main_json_gallery_data = new GalleryData();
        if(json!=""){        
            var temp_data = jQuery.parseJSON(json);
            main_json_gallery_data = jQuery.extend(new GalleryData(), temp_data);
        }                
        
        initAccordion();
        
        groups_model = new GroupsModel();
        groups_view = new GroupsView(jQuery('#accordion'), groups_model);
        if(main_json_gallery_data.getGroups()!=undefined){
            groups_model.setData(jQuery.extend(new Array(), main_json_gallery_data.getGroups()));
        }        
        //groups_model.onChange();
        
        jQuery('#addGroupBTN').click(function(e){
            e.preventDefault();
            EventBus.dispatchEvent(new Event(Event.ADD_NEW_GROUP));
        });
        
        
        //thumbs size
        var spinerW = jQuery('#spinnerThumbW').spinner({step: 1, min: 100, max: 500, spin: function(e, ui){                                 
        }});
        
        //thumbs gaps
        var spinerGap1 = jQuery('#spinnerGap01').spinner({step: 1, min: 0, max: 100, spin: function(e, ui){                                 
        }});
        
        
        jQuery('#lightbox_colors').ColorPicker({
            onSubmit: function(hsb, hex, rgb, el) {
                jQuery(el).val(hex);
                jQuery(el).ColorPickerHide();
            },
            onBeforeShow: function () {
                jQuery(this).ColorPickerSetColor(this.value);
            }                        
        });
        
        jQuery('#menu_text_color').ColorPicker({
            onSubmit: function(hsb, hex, rgb, el) {
                jQuery(el).val(hex);
                jQuery(el).ColorPickerHide();
            },
            onBeforeShow: function () {
                jQuery(this).ColorPickerSetColor(this.value);
            }                        
        });
        
        jQuery('#menu_back_color').ColorPicker({
            onSubmit: function(hsb, hex, rgb, el) {
                jQuery(el).val(hex);
                jQuery(el).ColorPickerHide();
            },
            onBeforeShow: function () {
                jQuery(this).ColorPickerSetColor(this.value);
            }                        
        });                                                      
        
    } 
    
    function initAccordion(){
        jQuery( "#accordion")
        .accordion({
        heightStyle: 'content',
        collapsible: true,
        icons: { "header": "ui-icon ui-icon-arrowthick-2-n-s", "activeHeader": "ui-icon ui-icon-arrowthick-2-n-s" },
        header: "> div > p"
        })
        .sortable({        
        axis: "y",
        handle: "p",        
        stop: function( event, ui ) {            
            reorderGroups();
        ui.item.children( "p" ).triggerHandler( "focusout" );
        }
        });        
    }
    
    
    /* reorder groups
    ================================================== */      
    function reorderGroups(){
        var list = new Array();
        jQuery('.galleryGroup').each(function(indx){
            list.push(jQuery(this).attr('data-groupID'));
        });
        EventBus.dispatchEvent(new Event(Event.REORDER_GALLERY_GROUPS, list));
    }
    
    /* templates
    ================================================== */
   
            
    //iGallery TEMPLATES
    function iGalTemplates(){
       
        //get incomming animations types
         this.getAvailableSizes = function(){             
            var obj = new Array();
            obj.push({val: "size1", name: 'None'});
            obj.push({val: "size2", name: 'Pop in'});
            obj.push({val: "size3", name: 'Fade in'});
            obj.push({val: "size4", name: 'Slide from left'});
            obj.push({val: "size5", name: 'Slide from right'});
            obj.push({val: "size6", name: 'Slide from top'});
            obj.push({val: "size7", name: 'Slide from bottom'});
            return obj;
        }        
        
       this.getGroupTemplate = function(groupModel){     
           var tmpl = '';
             tmpl += '<div class="group galleryGroup">'+
                '<p class="groupHeader">'+groupModel.getName()+'</p>'+
                '<div>'+
                    '<div class="groupControls">'+
                        '<label class="customLabel marginRightMedium">Group name:</label>'+                      
                        '<input class="inputText" type="text" name="" value="'+groupModel.getName()+'" />'+                                                                         
                        '<a style="line-height: 22px;" class="button-primary alignright addImagesBTN">Add images</a>'+
                        '<a style="margin-right: 10px;line-height: 22px;" class="button-secondary alignright removeGroupBTN">Remove group</a>'+
                        '<div class="hLine"></div>'+
                        '<ul class="itemsContainer"></ul>'+
                        '<div class="vspace1"></div>'+                       
                    '</div>'+
                '</div>'+
             '</div>';
           return tmpl;           
       }
    }
    iGalTemplates.getInstance=function(){
        if(iGalTemplates.instance==null){
            iGalTemplates.instance = new iGalTemplates();
        }
        return iGalTemplates.instance;
    }   
    
    
    
    /* gallery data
    ================================================== */       
    function GalleryData(){
        
        this.groups;
        this.setGroups = function(val){
           this.groups = val; 
        }
        this.getGroups = function(){
            return this.groups;
        }
    }
    
    
    /* edit caption
    ================================================== */     
    function EditCaption(imgModel){
        var captionContainer = jQuery('<div id="editCaptionContainer"></div>');
        TweenLite.to(captionContainer, 0, {css:{opacity:0}});
        captionContainer.appendTo('body');
        TweenLite.to(captionContainer, .2, {css:{opacity:1}, ease:Power3.EaseIn, onComplete: showCaptionDialog});
        
        function showCaptionDialog(){
            var captionDialog = jQuery('<div id="captionDialog" title="Image properties">'+
            '<p><b>Image caption:</b></p>'+
            '<textarea id="captionTexarea">'+imgModel.getCaption()+'</textarea>'+ 
            '<p><b>Sub title:</b></p>'+
            '<textarea id="captionTexarea2" style="width: 100%">'+imgModel.getSubCaption()+'</textarea>'+ 
            '<p><b>Thumb URL:</b></p>'+
            '<input id="rx_thumb_url" class="imageWidthInput" name="" value="'+imgModel.getImageURL()+'" />'+            
            '<div class="vspace2"></div>'+          
            '<div class="hLine"></div>'+
            '<p><b>Thumb height:</b></p>'+
            '<input id="rx_thumb_height" class="imageHeightInput" name="" value="'+imgModel.getImageHeight()+'" /><label class="customLabel">px</label>'+
            '<p><b>Thumb width:</b></p>'+
            '<input id="rx_thumb_width" class="imageWidthInput" name="" value="'+imgModel.getImageWidth()+'" /><label class="customLabel">px</label>'+
            '<div class=".clear-fx"></div>'+  
            '<div class="hLine"></div>'+         
            '<a class="button-primary alignright saveCaptionBTN">Save</a>'+
            '<div class=".clear-fx"></div>'+ 
            '<div class="vspace2"></div>'+  
            +'</div>');
            TweenLite.to(captionDialog, 0, {css:{opacity:0}});
            captionDialog.appendTo(captionContainer);
            TweenLite.to(captionDialog, .2, {css:{opacity:1}, ease:Power3.EaseIn});
            captionDialog.dialog({
                close: function(e, ui){
                    gcc_caption_container();
                }
            });
            
            var saveCaptionBTN = captionDialog.find('.saveCaptionBTN');
            saveCaptionBTN.click(function(e){
                e.preventDefault();
                var new_caption    = document.getElementById('captionTexarea').value;
                var new_subcaption = document.getElementById('captionTexarea2').value;
                imgModel.setCaption(new_caption);
                imgModel.setSubCaption(new_subcaption);
                imgModel.setImageHeight(jQuery('#rx_thumb_height').val());
                imgModel.setImageWidth(jQuery('#rx_thumb_width').val());
                imgModel.setImageURL(jQuery('#rx_thumb_url').val());
                EventBus.dispatchEvent(new Event(Event.GROUP_CHANGE));
                captionDialog.dialog("close");
            });
        }
        
        function gcc_caption_container(){
            try{
                captionContainer.remove();
                captionContainer = null;
                jQuery("#captionDialog").dialog( "destroy" );
            }catch(e){}          
        }
    }
    
      
}



