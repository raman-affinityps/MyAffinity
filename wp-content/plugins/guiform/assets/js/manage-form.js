/*!
 * GuiForm Plugin
 * https://www.guiform.com
 *
 * Author: Russell Pabon
 * http://russellpabon.com
 *
 */

(function() {
	
	var $ = jQuery;
	
	$('.guif-action').click( function(){
		
		var id = $('a', this).attr('id').split('-')[1];
		var action = $('a', this).attr('class');
		var location = guiform.entries_url;
		
		var data = {
					'action': "guiform_"+action, 
					'id'    : id,
					'form' : $('#form').val()
				};	
		
		$('<div>').guifbox({
			title:'Please confirm your action.',
			status:'confirm',
			body:'<p>Are you sure?</p>',
			url: ajaxurl,
			data: data,
			dataType: 'html',
			cache: false,
			type: 'POST',
			success: function(result){
				var type = (action == 'delete-form') ? 'form' : 'entry';
				if(result != ''){
					var text = $(result).text().split('[')[1].split(']')[0]
					$(this).guifbox({title: 'error', status: 'error', body: result});
				}
				else{
					$(this).guifbox({reload: true, location: location, title: 'message', status: 'success', body: '<p>Successful.</p>'});
				}
			}
		})
			
		return false;
				
	})
	
	function column(event){
		var size = $(".wp-list-table thead th").size();
		var hide = $(".wp-list-table thead th[style*='display']").size();
		$(".wp-list-table .inline-edit-row td").attr('colspan', size - hide);
	};
	
	$('.meta-box-sortables').sortable({
		start: function(event, ui){
			$("#guiform .meta-box-sortables").css({'min-height':'100px'})
		},
		beforeStop: function(){
			$("#guiform .meta-box-sortables").css({'min-height':'inherit'})
		}
	})
	
	$('.quick_edit').click(function(){
		var id = $(this).find('a').attr('id').split('-')[1];
		var parent = $(this).parent().parent().parent();
		$('#the-list tr').show();	
		parent.addClass('hide').hide();
		$('#the-list tr:eq('+parent.index()+')').after("<tr id='loader'><td colspan='0' align='center'><img src='"+ guiform.plugins_url +"images/save-loader.gif'></td></tr>");
		var data = {
					'action': 'guiform_form_quick_edit', 
					'id' : id
				};
		
	  $.ajax({
			url: ajaxurl,
			data: data,
			dataType: 'html',
			cache: false,
			async: false,
			type: 'POST',
			success:function(result){
				$('#loader').remove();
				$('#the-list tr:eq('+parent.index()+')').after(result);
				 column();
			}
		})
		
	});
	
	$('.wp-list-table').on('click', '.button-secondary.cancel', function(){
		$('.inline-edit-row').remove();
		$('#the-list tr').show(600);	
	});
	
	
	$('.wp-list-table').on('click', '.button-primary.save', function(){
		
		var id = $.trim($("input[name=form-id]").val());
		var title = $.trim($("input[name=title]").val()) || 'Untitled';
		var save_entries = ($("input[name=save_entries]").is(":checked")) ? 1 : 0;

		$('.spinner').show();
		
		var data = {
					'action': 'guiform_form_quick_edit_save', 
					'id' : id,
					'title' : title,
					'save_entries' : save_entries
				};
		
		$.ajax({
			url: ajaxurl,
			data: data,
			dataType: 'html',
			cache: false,
			async: false,
			type: 'POST',
			success:function(data){
				$('.spinner').hide();
				data = JSON.parse(data);
				if(data.status == "error"){
					$('<div>').guifbox({title: data.status, status: data.status, body: '<p>'+ data.message +'</p>'});
				}
				else{
					$('<div>').guifbox({reload: true, location: location.href, title: 'message', status: 'success', body: '<p>'+ data.message +'</p>'});	
				}
				
			}
		})
		
	});
	
}).call(this);