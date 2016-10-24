/*!
 * GuiForm Plugin
 * https://www.guiform.com
 *
 * Author: Russell Pabon
 * http://russellpabon.com
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 *	jquery.ui.draggable.js
 */
(function( $, undefined ) {

	var options = {
      reload  : false,
			bgColor : '#000000',
			opacity : .5,
			title   : 'Loading',
			body    : '',
			status  : 'loading', //Posible value: message, confirm, error, success and loading
			overlay : true,
			result  : '',
			data    : '',
			dataType: 'html',
			cache   : false,
			url     : '',
			maxScreen: false,
			maxHeight : 'auto',
			width   : 'auto',
			type    : 'POST',
			center  : true,
			showSpeed : 400,
			cancelButton : true,
			saveButton : false,
			closeButton : false,
			fullScreenButton: false,
			cancel  : function(){},
			close   : function(){},
			drag    : function(){},
			fullScreen : function(status){},
			htmlButton : function(){},
			save : function(){},
			confirm : function(){},
			complete : function(){},
			error : function(){},
			create :function(){}
    }
    
	$.widget( "guiform.guifbox", $.extend({}, options, {

    _create: function(){
    	var call = this;
    	var options = this.options;
    	var status;
    	this._build(); 
    	
    	$('#guifbox').on('click', '#guifbox-fullscreen',function(){
    		if($("#guifbox-window").hasClass('guifbox-maxscreen')){
    			 $("#guifbox-window").removeClass('guifbox-maxscreen')
  			 	 $('body').css('overflow','')
  			 	 $('#guifbox-body').css('height', 'auto')
  			 	 status = false;
    		}
    		else{
  			 	 $("#guifbox-window").addClass('guifbox-maxscreen');
  			 	 $('#guifbox-body').css('height',$(window).height() - 113)
  			 	 $('body').css('overflow','hidden');
  			 	 status = true;
  			}
    		options.fullScreen(status);
    	})
    	
    	$('#guifbox').on('click', '#guifbox-save',function(){
    		$('#guifbox-spinner').show();
    		
    		$.when(options.save()).then(options.complete()).then(function(){
    			$('#guifbox-window').animate({opacity: 0}, 600, function(){
	  				$('#guifbox').empty();
						$('#guifbox').remove();
					});
    		})
    	})
    },
		
    _maxscreen: function(){
    	$("#guifbox-window").addClass('guifbox-maxscreen');
    },
    
    _init: function(){
    	this._show();
    	this._ui();
			this._event();
			if(this.options.maxScreen == true){
				this._maxscreen();
				this.options.fullScreen(true);
				$('body').css({'overflow' : 'hidden'})
			}
    },
    
    _ui: function(){
    	var options = this.options;
    	if(typeof(jQuery.ui.draggable) != 'undefined'){
			  $('#guifbox-window').draggable({
			  	cursor: 'move',
			  	handle: 'table, #guifbox-header, .ui-tabs-nav, #tab-options, #tab-file',
			  	cancel: "#options-list, input, button, select, textarea, .dropdown-menu, .notes",
			  	start: function(event, ui){
			  		options.drag();
			  	}
			  })
			  
				$('#guifbox-header').css('cursor','move');
			}
    },	

    _build: function() {
        var bgHeader;
				$('#guifbox').remove();
				if(this.options.status == 'error') bgHeader = '#FF0000'; 
				if(this.options.status == 'confirm' || this.options.status == 'message' || this.options.status == 'loading') bgHeader = '#21759B'; 
				if(this.options.status == 'success') bgHeader = '#00853F'; 
    	
      	html = "<div id='guifbox' class='guiform'>\
								<div id='guifbox-lockscreen'></div>\
								<div id='guifbox-overlay'></div>\
								<div id='guifbox-window'>\
									<div id='guifbox-header'>\
										<span id='guifbox-title'></span>\
									</div>\
									<div id='guifbox-body'></div>\
								</div>\
							</div>";
							
				($('#guifbox').size() > 0) ? $('#guifbox-window, #guifbox-overlay').show() : $('body').append(html);			
				
				(this.options.status != 'loading') ? $('#guifbox-header').append('<span class="fa fa-close" id="guifbox-close"></span>') : $('#guifbox-close').remove();		
				
				(this.options.fullScreenButton == true) ? $('#guifbox-header').append("<span id='guifbox-fullscreen' title='Full Screen'></span>") : $('#guifbox-fullscreen').remove();		
				
				(this.options.status == 'loading' || this.options.status == 'confirm') ? $('#guifbox-lockscreen').show() : $('#guifbox-lockscreen').hide();		
				
				$('#guifbox-overlay').css({'background-color': this.options.bgColor});
				$('#guifbox-header').css({'background-color': bgHeader});
				$('#guifbox-title').html(this.options.title.toUpperCase());
				$('#guifbox-body').html(this.options.body);
				
				$('#guifbox-body').css({'width': this.options.width});
				
				if(this.options.status == 'confirm'){
					var cancelButton = '';
					 if(this.options.cancelButton !== false) cancelButton = "<a id='guifbox-cancel' class='button-secondary' href='javascript:void(0)'>Cancel</a> ";
												
					var html = "<div id='guifbox-button'>"+
												cancelButton
												+"<a id='guifbox-confirm' class='button-primary' href='javascript:void(0);' onclick='return false;'>Confirm</a>\
											</div>";
					$('#guifbox-window').append(html);
				}
				
				if(this.options.saveButton == true){
					$('#guifbox-button').prepend("<span id='guifbox-spinner'></span>");
					$('.button-primary').attr('id', 'guifbox-save').text('Save');
				}
				
				if(this.options.closeButton == true) $('#guifbox-button').prepend("<a id='guifbox-close' class='button-secondary' href='javascript:void(0);' onclick='return false;'>Close</a>");
				if(this.options.confirmButton == false) $('#guifbox-confirm').remove();
					
				if(this.options.overlay == false) $('#guifbox-overlay, #guifbox-lockscreen').remove();
				
				this._createHTML();
				
    },
    
    _createHTML: function(){
    	this.options.create();
      $('#guifbox-body .ui-tabs-panel').css({'overflow': 'auto', 'max-height' : this.options.maxHeight});
			
    },
    
    _show: function(){
    	var options = this.options;
    	
    	if($("#GuiForm.form-builder").size() == 0 || options.center){
				var left = ($(document).width() - $('#guifbox-window').width()) / 2;
				$('#guifbox-window').css({'opacity':'0','left': left,'top':'-1000px'}).animate({'width':'auto'}, 1, function(){
					var top = ($(window).height() - $('#guifbox-window').height()) / 2;
					$(this).animate({'opacity':'1','left': left,'top':top}, options.showSpeed);
				});
			}
			
			$('#guifbox-overlay').css({'background-color': options.bgColor, opacity: options.opacity});
    },
    
    _event: function() {
    	$doc = $(document);
    	var options = this.options;
    	var method = this;
			
    	if(options.status != 'confirm' && options.url != '') method._confirm();
			
    	$('#guifbox-confirm').click( function(){
				(options.url != '') ?	method._confirm(options) : options.confirm();
			})
			
			$doc.on('click', '#guifbox-overlay, #guifbox-close, #guifbox-cancel', function(){
				method._close();
			});
			
			
			$doc.keydown(function(event){
				var key = event.charCode || event.keyCode || 0; 
				if($('#guifbox').size() > 0 && key == 27){
					method._close();
				}
			})
			
    },
    
    _close : function(){
    	$doc = $(document);
    	var method = this;
    	var options = this.options;
    	if(options.status == 'success' && options.reload == true){
				$('#guifbox-header').css({'background-color': '#21759B'});
				$('#guifbox-title').html('Loading');
				$('#guifbox-button, #guifbox-close').remove();
				$('#guifbox-body').html('<p style="padding: 10px;>Page Loading</p>');
				options.reload = true
				options.status = 'loading';
				$('#guifbox').remove()
				$(method.element).remove();
				window.location.href = options.location || location.href.toString();
			}
			else if(options.status == 'success'){
				$('#guifbox-window').animate({'opacity': '0'}, 600, function(){
					$('#guifbox').remove()
				});
			}
			
		  if(options.status == 'message' || options.status == 'error' || options.status == 'confirm'){
				options.cancel();
				$('#guifbox-window').animate({'opacity': '0'}, 600, function(){
					$('#guifbox').remove()
					$(method.element).remove();
					$('body').css('overflow','')
					options.close();
				});
			}
    },
    
    _confirm : function(options){
    	var method = this;
			$.ajax({
				dataType: this.options.dataType,
				cache: this.options.cache,
				type: this.options.type,
				data: this.options.data,
				url: this.options.url,
				error: function( jqXHR, textStatus, errorThrown ){
					if(jqXHR.readyState == 4){
						$('<div />').guifbox({title: 'error', status: 'error', body: '<p style="padding: 10px;">'+ jqXHR.responseText +'</p>'});
					}
				},
				beforeSend: function(){
					$('#guifbox-header').css({'background-color': '#21759B'});
					$('#guifbox-title').html('Loading');
					$('#guifbox-button, #guifbox-close').remove();
					$('#guifbox-body').html('<p style="padding: 10px;">Please wait while the server processing your request.</p>');
					var top = ($('body').height() - $('#guifbox-window').height()) / 2;
					var left = ($('body').width() - $('#guifbox-window').width()) / 2;
					$('#guifbox-window').css({'top': top, 'left' : left});
				},
				success: function(result){
					$('#guifbox').remove();
					$(method.element).remove();
					method.options.success(result);
				}
			})
		}
		
	}));
	
	$.guiform.guifbox.prototype.options = $.extend({}, $.guiform.guifbox.prototype.options, options);

}(jQuery));