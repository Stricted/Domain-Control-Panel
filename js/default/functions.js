$(document).ready(function(){
	function showError (msg) {
		$('#errorConfirmModal').remove();
		$('#page-wrapper').append('<div class="modal fade" id="errorConfirmModal" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true" ><i class="fa fa-times-circle"></i></button><h4 class="modal-title">' + language['javascript.error.title'] + '</h4></div><div class="modal-body"></div><div class="modal-footer"><button class="btn" data-dismiss="modal" aria-hidden="true">' + language['javascript.close'] + '</button></div></div></div></div>');
		$('#errorConfirmModal').find('.modal-body').text(msg);
		$('#errorConfirmModal').modal({show:true});
	}
	
	/* delete box */
	(function(){
		var t = undefined;
		$('span[delete-confirm]').unbind('click');
		$('span[delete-confirm]').on('click', function(ev) {
			ev.preventDefault();
			t = $(this);
			
			$('#dataConfirmModal').remove();
			$('body').append('<div class="modal fade" id="dataConfirmModal" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true" ><i class="fa fa-times-circle"></i></button><h4 class="modal-title">' + language['javascript.confirm'] + '</h4></div><div class="modal-body"></div><div class="modal-footer"><a class="btn btn-primary" id="dataConfirmOK">OK</a><button class="btn" data-dismiss="modal" aria-hidden="true">' + language['javascript.close'] + '</button></div></div></div></div>');			
			$('#dataConfirmModal').find('.modal-body').text(t.attr('delete-confirm'));
			
			$("#dataConfirmOK").unbind('click');
			$("#dataConfirmOK").on('click', function(e){
				e.preventDefault();
				$('#dataConfirmModal').modal('hide');
				var action = undefined;
				var deleteID = t.attr('delete-id');
				if (deleteID) {
					if (t.hasClass('deleteDomain')) {
						action = 'deleteDomain';
					}
					else if (t.hasClass('deleteRecord')) {
						action = 'deleteRecord';
					}
					else if (t.hasClass('deleteSec')) {
						action = 'deleteSec';
					}
					else if (t.hasClass('deleteUser')) {
						action = 'deleteUser';
					}
					else {
						showError(language['javascript.error']);
						return false;
					}
				
					$.ajax({
						url: 'index.php?page=action',
						data: {
							action: action,
							dataID: deleteID
						},
						type: 'post',
						success: function(output) {
							if (output == 'success') {
								t.parent().parent().remove();
							}
							else {
								showError(language['javascript.error']);
							}
						}
					});
				}
				else {
					showError(language['javascript.error']);
				}
				
				return false;
			});
			
			$('#dataConfirmModal').modal({show:true});
			return false;
		});
	})();
	
	/* API key */
	(function(){
		$('#requestApiKey').unbind('click');
		$("#requestApiKey").on('click', function(e){
			$.ajax({
				url: 'index.php?page=action',
				data: {
					action: 'requestApiKey',
					dataID: 1
				},
				type: 'post',
				success: function(output) {
					if (output == 'failure') {
						showError(language['javascript.error']);
					}
					else {
						$('#apiKey').text(output);
					}
				}
			});
		});
	})();
	
	/* toggle */
	(function(){
		$('span[toggle-id]').unbind('click');
		$('span[toggle-id]').on('click', function(ev) {
			ev.preventDefault();
			var t = $(this);
			var action = undefined;
			var dataID = t.attr('toggle-id');
			if (dataID) {
				if (t.hasClass('toggleDomain')) {
					action = 'toggleDomain';
				}
				else if (t.hasClass('toggleRecord')) {
					action = 'toggleRecord';
				}
				else if (t.hasClass('toggleSec')) {
					action = 'toggleSec';
				}
				else if (t.hasClass('toggleUser')) {
					action = 'toggleUser';
				}
				else {
					showError(language['javascript.error']);
					return false;
				}
				
				$.ajax({
					url: 'index.php?page=action',
					data: {
						action: action,
						dataID: dataID
					},
					type: 'post',
					success: function(output) {
						if (output == 'success') {
							// toggle
							if (t.hasClass('fa-square-o')) {
								// set enabled
								t.removeClass('fa-square-o').addClass('fa-check-square-o');
								t.tooltip('hide').attr('data-original-title', t.attr('data-disable-message')).tooltip('fixTitle').tooltip('show');
								t.parent().parent().children().first().next().find("span.badge").remove();
							}
							else if (t.hasClass('fa-check-square-o')) {
								// set disabled
								t.removeClass('fa-check-square-o').addClass('fa-square-o');
								t.tooltip('hide').attr('data-original-title', t.attr('data-enable-message')).tooltip('fixTitle').tooltip('show');
								t.parent().parent().children().first().next().prepend('<span class="badge badge-red">' + language['domain.disabled'] + '</span> ');
							}
							else {
								showError(language['javascript.error']);
							}
						}
						else {
							showError(language['javascript.error']);
						}
					}
				});
			}
			else {
				showError(language['javascript.error']);
			}

			return false;
		});
	})();
	
	/* Bootstrap Tooltips */
	(function(){
		$('.ttips').each(function(e) {
			$(this).tooltip();
		});
	})();
	
	/* dns input fields */
	(function(){
		$('#type').unbind('keyup keydown keypress change');
		$('#type').on('keyup keydown keypress change', function () {
			var val = $.trim($(this).val());
			// default data
			$('dl#aux').find('dt').text('Prio');
			$('dl#weight').find('dt').text('weight');
			$('dl#port').find('dt').text('port');
			$('dl#data').find('dt').text('Data');
			$("dl#aux").hide();
			$("dl#weight").hide();
			$("dl#port").hide();
			$("dl#data").hide();
			
			switch (val) {
				case "A":
				case "AAAA":
				case "CNAME":
				case "TXT":
				case "NS":
				case "PTR":
					$("dl#data").show();
					break;
				
				case "DS":
					$("dl#aux").show(); // key tag
					$("dl#weight").show(); // algorithm
					$("dl#port").show(); // algorithm type
					$("dl#data").show(); // digest
					$('dl#aux').find('dt').text('Key-ID');
					$('dl#weight').find('dt').text('Algorithm');
					$('dl#port').find('dt').text('Digest Type');
					$('dl#data').find('dt').text('Digest');
					break;
					
				case "TLSA":
					$("dl#aux").show(); // Usages
					$("dl#weight").show(); // Selectors
					$("dl#port").show(); // Types
					$("dl#data").show(); // Hash
					$('dl#aux').find('dt').text('Usage');
					$('dl#weight').find('dt').text('Selector');
					$('dl#port').find('dt').text('Hash Type');
					$('dl#data').find('dt').text('Hash');
					break;				
				
				case "MX":
				/*case "DNSKEY":*/
					$("dl#aux").show();
					$("dl#data").show();
					break;
				
				case "SRV":
					$("dl#aux").show(); // priority
					$("dl#weight").show(); // weight
					$("dl#port").show(); // port
					$("dl#data").show(); // target
					break;
			}

		});
	})();
	
	/* export */
	(function(){
		$('#export').unbind('click');
		$('#export').on('click', function(e) {
			console.log('click');
			var t = $(this);
			var dataID = t.attr('export-id');
			$.ajax({
				url: 'index.php?page=action',
				data: {
					action: 'export',
					dataID: dataID
				},
				type: 'post',
				success: function(output) {
					console.log(output);
					if (output == 'failure') {
						//do nothing
						/*showError(language['javascript.error']);*/
					}
					else {
						/* show modal with zone file */
						$('#exportModal').remove();
						$('#page-wrapper').append('<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-hidden="true">' +
							'<div class="modal-dialog">' +
								'<div class="modal-content">' +
									'<div class="modal-header">' +
										'<button type="button" class="close" data-dismiss="modal" aria-hidden="true" ><i class="fa fa-times-circle"></i></button>' +
										'<h4 class="modal-title">Zone File Export</h4>' +
									'</div>' +
									'<div class="modal-body">' +
										'<textarea id="exportTextarea" style="width: 569px; height: 552px;">' + output + '</textarea>' +
									'</div>' +
									'<div class="modal-footer">' +
									'<button class="btn" data-dismiss="modal" aria-hidden="true">' + language['javascript.close'] + '</button>' +
									'</div>' +
								'</div>' +
							'</div>' +
						'</div>');
						$('#exportModal').modal({show:true});
						setTimeout(function() {
							$('#exportTextarea').focus();
						}, 700);
					}
				}
			});
		});
	})();	
	
	/* import */
	(function(){
		$('#import').unbind('click');
		$('#import').on('click', function(e) {
			var t = $(this);
			var dataID = t.attr('import-id');
			$('#importModal').remove();
			$('#page-wrapper').append('<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">' +
				'<div class="modal-dialog">' +
					'<div class="modal-content">' +
						'<div class="modal-header">' +
							'<button type="button" class="close" data-dismiss="modal" aria-hidden="true" ><i class="fa fa-times-circle"></i></button>' +
							'<h4 class="modal-title">Zone File Import</h4>' +
						'</div>' +
						'<div class="modal-body">' +
							(dataID ? '' : '<input type="text" id="importOrigin" value="" placeholder="example.com." style="width: 100%;"/>') +
							'<textarea id="importTextarea" style="width: 569px; height: 552px;"></textarea>' +
						'</div>' +
						'<div class="modal-footer">' +
						'<button class="btn" id="importSubmit" aria-hidden="true">OK</button>' +
						'<button class="btn" data-dismiss="modal" aria-hidden="true">' + language['javascript.close'] + '</button>' +
						'</div>' +
					'</div>' +
				'</div>' +
			'</div>');
			$('#importModal').modal({show:true});
			
			setTimeout(function() {
				$('#importTextarea').focus();
			}, 700);
			
			$("#importSubmit").unbind('click');
			$("#importSubmit").on('click', function(e){
				var origin = $('#importModal').find('.modal-body').find('#importOrigin').val();
				var zone = $('#importModal').find('.modal-body').find('#importTextarea').val();
				$.ajax({
					url: 'index.php?page=action',
					data: {
						action: 'import',
						dataID: dataID ? dataID : 0, /* 0 for new zone otherwise soaID for existing zone*/
						origin: origin ? origin : '',
						zone: zone
					},
					type: 'post',
					success: function(output) {
						$('#importModal').modal('hide');
						if (output == 'failure') {
							showError(language['javascript.error']);
						}
						else {
							if (!dataID) {
								// redirect to main page on success
								$(location).attr('href','index.php?page=DomainList');
							}
							else {
								// redirect to record list on success
								$(location).attr('href','index.php?page=RecordList&id=' + dataID);
							}
						}
					}
				});
			});

		});
	})();
	
	/* metis Menu */
	(function(){
		$('#side-menu').metisMenu();
	})();
});

$(window).load(function(){
	// Loads the correct sidebar on window load,
	// collapses the sidebar on window resize.
	// Sets the min-height of #page-wrapper to window size
	(function(){
		$(window).bind("load resize", function() {
			topOffset = 50;
			width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
			if (width < 768) {
				$('div.navbar-collapse').addClass('collapse');
				topOffset = 100; // 2-row-menu
			} else {
				$('div.navbar-collapse').removeClass('collapse');
			}

			height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
			height = height - topOffset;
			if (height < 1) height = 1;
			if (height > topOffset) {
				$("#page-wrapper").css("min-height", (height) + "px");
			}
		});
	})();
});
