var $body = $('body');

$(function() {

	// Move an element up and down in DOM
	$.fn.moveUp = function(callback) {
		$.each(this, function() {
			$(this).after($(this).prev());
		});
		callback();
	};
	$.fn.moveDown = function(callback) {
		$.each(this, function() {
			$(this).before($(this).next());
		});
		callback();
	};

	var $easyvoteEducationContentContainer = $('#easyvoteeducation-content');

	// Load dashboard on startup
	if ($easyvoteEducationContentContainer.length) {
		if (document.location.hash) {
			EasyvoteEducation.callHashRequestedAction();
		} else {
			// Fall back to dashboard
			EasyvoteEducation.loadAction('dashboard');
		}
	}

	// AJAX-based general actions such as main navigation
	$body.on('click', "a[data-role='generalaction']", function(e) {
		e.preventDefault();
		var $this = $(this);
		var actionName = $this.attr('data-actionname');
		EasyvoteEducation.loadAction(actionName);
	});

	// AJAX-based actions for panels
	$body.on('click', "a[data-role='panelaction']", function(e) {
		e.stopPropagation();
		e.preventDefault();
		var $this = $(this);
		var actionName = $this.attr('data-actionname');
		var objectName = $this.attr('data-object');
		var objectUid = $this.attr('data-uid');
		EasyvoteEducation.pushHistoryState(actionName, objectName, objectUid);

		var confirmAction = $this.attr('data-confirm') === 'true';
		if (confirmAction) {
			// open a modal and wait for confirmation to continue
			Easyvote.displayModal($this.parent().find('.ajaxobject-confirm').html(), function(status) {
				EasyvoteEducation.performAjaxObjectAction(actionName, objectName, objectUid);
			})
		} else {
			// no confirmation needed, call the action right away
			EasyvoteEducation.performAjaxObjectAction(actionName, objectName, objectUid);
		}
	});

	// Implement AJAX actions for generic object actions (voting, votingOption)
	$body.on('click', "[data-role='genericobjectaction']", function(e) {
		e.stopPropagation();
		e.preventDefault();
		var $this = $(this);
		var actionName = $this.attr('data-actionname');
		var objectName = $this.attr('data-object');
		var objectUid = $this.attr('data-uid');
		var targetElement = '.' + objectName + '-item-' + objectUid;

		var confirmAction = $this.attr('data-confirm') === 'true';
		if (confirmAction) {
			// open a modal and wait for confirmation to continue
			Easyvote.displayModal($this.next('.ajaxobject-confirm').html(), function(selection) {
				EasyvoteEducation.performAjaxObjectAction(actionName, objectName, objectUid, targetElement, selection);
			})
		} else {
			// no confirmation needed, call the action right away
			EasyvoteEducation.performAjaxObjectAction(actionName, objectName, objectUid, targetElement, null, function() {
			});
		}
	});

	// Move generic objects
	$body.on('click', "[data-role='movegenericobject']", function(e) {
		e.stopPropagation();
		e.preventDefault();
		var $this = $(this);
		var actionName = $this.attr('data-actionname');
		var objectName = $this.attr('data-object');
		var objectUid = $this.attr('data-uid');
		var parentObjectName = $this.attr('data-parentobject');
		var parentObjectUid = $this.attr('data-parentuid');
		var direction = $this.attr('data-direction');
		var itemSelector = '.' + objectName + '-item-' + objectUid;

		if (direction == 'up') {
			$(itemSelector).moveUp(function() {
				EasyvoteEducation.persistSorting(actionName, objectName, parentObjectName, parentObjectUid);
			});
		} else {
			$(itemSelector).moveDown(function() {
				EasyvoteEducation.persistSorting(actionName, objectName, parentObjectName, parentObjectUid);
			});
		}
	});

	// React on history changes
	if (Modernizr.history) {
		window.onpopstate = function () {
			EasyvoteEducation.callHashRequestedAction();
		};
	}

	// Create panel
	$body.on('submit', '#newPanel', function(e) {
		e.preventDefault();
		EasyvoteEducation.postForm($(this).serialize(), EasyvoteEducationActionUris['create']).done(function(data) {
			jsonData = JSON && JSON.parse(data) || $.parseJSON(data);
			if (jsonData.hasOwnProperty('redirectToAction')) {
				EasyvoteEducation.loadAction(jsonData.redirectToAction);
			} else {
				// todo meaningful and usable error
				alert('Fehler!');
			}
		});
	});

	// Update panel
	$body.on('submit', '#editPanel', function(e) {
		e.preventDefault();
		EasyvoteEducation.postForm($(this).serialize(), EasyvoteEducationActionUris['update']).done(function(data) {
			jsonData = JSON && JSON.parse(data) || $.parseJSON(data);
			if (jsonData.hasOwnProperty('redirectToAction')) {
				EasyvoteEducation.loadAction(jsonData.redirectToAction);
			} else {
				// todo meaningful and usable error
				alert('Fehler!');
			}
		});
	});

	// Update voting
	$body.on('submit', '.editVoting', function(e) {
		e.preventDefault();
		$this = $(this);
		var objectName = $this.attr('data-objectname');
		var objectUid = $this.attr('data-objectuid');
		var targetSelector = '.' + objectName + '-item-' + objectUid;
		EasyvoteEducation.postForm($this.serialize(), EasyvoteEducationActionUris['updateVoting']).done(function(data) {
			jsonData = JSON && JSON.parse(data) || $.parseJSON(data);
			if (jsonData.hasOwnProperty('success') && jsonData.success === true) {
				EasyvoteEducation.performAjaxObjectAction('editVoting', objectName, objectUid, targetSelector, null, function() {
					// open the edited voting
					$(targetSelector + ' > .item-header').trigger('click');
				});
			} else {
				// todo meaningful and usable error
				alert('Fehler!');
			}
		});
	});

	// Update votingOption
	$body.on('submit', '.editVotingOption', function(e) {
		e.preventDefault();
		$this = $(this);
		var data = new FormData();
		$this.serializeArray().forEach(function(field) {
			data.append(field.name, field.value)
		});
		var $fileUploadField = $this.find('.votingOption-image').first();
		var files = $fileUploadField.prop('files');
		if (files.length) {
			data.append('tx_easyvoteeducation_managepanels[votingOption][image]', files[0]);
		}
		var parentObjectName = $this.attr('data-parentobjectname');
		var parentObjectUid = $this.attr('data-parentobjectuid');
		var targetSelector = '.' + parentObjectName + '-item-' + parentObjectUid;
		EasyvoteEducation.postFormWithFileUpload(data, EasyvoteEducationActionUris['updateVotingOption']).done(function(data) {
			jsonData = JSON && JSON.parse(data) || $.parseJSON(data);
			if (jsonData.hasOwnProperty('success') && jsonData.success === true) {
				EasyvoteEducation.performAjaxObjectAction('editVoting', parentObjectName, parentObjectUid, targetSelector, null, function() {
					// open voting
					$(targetSelector + ' > .item-header').trigger('click');
				});
			} else {
				// todo meaningful and usable error
				alert('Fehler!');
			}
		});
	});

	// Expandable items
	$body.on('click', '.expandable-item .item-header', function() {
		var $this = $(this);
		var $toolbar = $this.parent().children('.item-content');
		$toolbar.slideToggle(100);
	});

	// votingDuration
	$body.on('input change', '.votingDuration-slider', function() {
		var $container = $(this).closest('.editVoting');
		var votingDuration = this.value;
		$container.find('.votingDuration-value span').text(votingDuration);
		$container.find('.votingDuration').val(votingDuration + '');
	});

	$body.on('change', '.votingDuration-infinite', function() {
		var checked = this.checked;
		var $container = $(this).closest('.editVoting');
		var votingDuration = this.value;
		if (checked) {
			$container.find('.votingDuration-display').hide();
			$container.find('.votingDuration').val('0');
		} else {
			$container.find('.votingDuration-display').show();
			$container.find('.votingDuration-value span').text('60');
			$container.find('.votingDuration').val('60');
			$container.find('.votingDuration-slider').val('60');
		}
	});

	// votingOption image
	$body.on('change', '.votingOption-image', function() {
		var $this = $(this);
		var imagePreviewSelector = $this.parent().find('.votingOption-image-preview').attr('id');
		Easyvote.readFile(this, '#' + imagePreviewSelector);
	});

	// Datepicker field
	if ($.fn.datetimepicker) {
		$('.easyvoteEducation-date').each(function() {
			var $this = $(this);
			// stop javascript datepicker, if browser supports type="date" or "datetime-local" or "time"
			if ($this.prop('type') === 'date' || $this.prop('type') === 'datetime-local' || $this.prop('type') === 'time') {
				if ($this.data('datepicker-force')) {
					// rewrite input type
					$this.prop('type', 'text');
				} else {
					// stop js datepicker
					return;
				}
			}

			var datepickerStatus = true;
			var timepickerStatus = true;
			if ($this.data('datepicker-settings') === 'date') {
				timepickerStatus = false;
			} else if ($this.data('datepicker-settings') === 'time') {
				datepickerStatus = false;
			}

			// create datepicker
			$this.datetimepicker({
				format: $this.data('datepicker-format'),
				timepicker: timepickerStatus,
				datepicker: datepickerStatus,
				lang: 'en',
				i18n:{
					en:{
						months: $this.data('datepicker-months').split(','),
						dayOfWeek: $this.data('datepicker-days').split(',')
					}
				}
			});
		});
	}

});


var EasyvoteEducation = {

	/**
	 * Load content by AJAX
	 * @param uri
	 * @returns {*}
	 */
	loadAjaxContent: function(uri) {
		return $.ajax({
			url: uri
		});
	},

	/**
	 * Load an action and write its result to a container
	 *
	 * @param actionName Name of the action, an URI with the same name must be defined
	 * @param contentContainerSelector
	 */
	loadAction: function(actionName, contentContainerSelector) {
		if (typeof(contentContainerSelector) === 'string') {
			var $container = $(contentContainerSelector);
		} else {
			var $container = $('#easyvoteeducation-content');
		}
		EasyvoteEducation.loadAjaxContent(EasyvoteEducationActionUris[actionName]).done(function(data) {
			$container.html(data);
			EasyvoteEducation.pushHistoryState(actionName);
			Easyvote.bindPostalCodeSelection();
		});
	},

	/**
	 * @param objectName
	 * @param objectUid
	 * @param uri
	 * @param selection
	 * @returns {*}
	 */
	loadAjaxObjectContent: function(objectName, objectUid, uri, selection) {
		var data = {};
		data['tx_easyvoteeducation_managepanels'] = {};
		data['tx_easyvoteeducation_managepanels'][objectName] = objectUid;
		if (selection) {
			data['tx_easyvoteeducation_managepanels']['selection'] = selection;
		}
		return $.ajax({
			type: "POST",
			url: uri,
			data: data
		});
	},

	performAjaxObjectAction: function(actionName, objectName, objectUid, contentContainerSelector, selection, callback) {
		if (typeof(contentContainerSelector) === 'string') {
			var $container = $(contentContainerSelector);
		} else {
			var $container = $('#easyvoteeducation-content');
		}
		var actionUri = EasyvoteEducationActionUris[actionName];
		EasyvoteEducation.loadAjaxObjectContent(objectName, objectUid, actionUri, selection).done(function (data) {
			jsonData = JSON && JSON.parse(data) || $.parseJSON(data);
			if (jsonData.hasOwnProperty('redirectToAction')) {
				EasyvoteEducation.loadAction(jsonData.redirectToAction, contentContainerSelector);
			} else if (jsonData.hasOwnProperty('removeElement')) {
				// Remove the target container, e.g. after a delete action
				$container.slideUp(200, function() {
					$container.remove();
					if (callback) {
						callback();
					}
				});
			} else if (jsonData.hasOwnProperty('reloadVotings')) {
				// Remove the target container, e.g. after a delete action
				EasyvoteEducation.performAjaxObjectAction('listForCurrentUser', 'panel', jsonData.reloadVotings, '.votings-content', null, callback);
			} else if (jsonData.hasOwnProperty('reloadVotingOptions')) {
				// Remove the target container, e.g. after a delete action
				EasyvoteEducation.performAjaxObjectAction('listForVoting', 'voting', jsonData.reloadVotingOptions, '.votingOptions-content', null, callback);
			} else {
				$container.html(jsonData.content);
				Easyvote.bindPostalCodeSelection();
				if (callback) {
					callback();
				}
			}
		});
	},

	postForm: function(data, uri) {
		return $.ajax({
			type: "POST",
			url: uri,
			data: data
		});
	},

	postFormWithFileUpload: function(data, uri) {
		return $.ajax({
			type: 'POST',
			url: uri,
			data: data,
			cache: false,
			contentType: false,
			processData: false
		});

	},

	/**
	 * Persist the sorting of an objectStorage from its ordner in the DOM
	 * @param actionName
	 * @param objectName
	 */
	persistSorting: function(actionName, objectName, parentObjectName, parentObjectUid) {
		var $objects = $('.' + objectName + '-item');
		var sorting = 1;
		var data = {};
		data['tx_easyvoteeducation_managepanels'] = {};
		data['tx_easyvoteeducation_managepanels']['sorting'] = {};
		data['tx_easyvoteeducation_managepanels'][parentObjectName] = parentObjectUid;
		$objects.each(function() {
			var objectUid = $(this).attr('data-uid');
			data['tx_easyvoteeducation_managepanels']['sorting'][objectUid] = sorting;
			sorting++;
		});
		EasyvoteEducation.postForm(data, EasyvoteEducationActionUris[actionName]).done(function(data) {
			jsonData = JSON && JSON.parse(data) || $.parseJSON(data);
			if (!jsonData.hasOwnProperty('success')) {
				// todo use a meaningful error message
				alert('Fehler');
			}
		});
	},

	pushHistoryState: function(actionName, objectName, objectUid) {
		if (Modernizr.history) {
			var currentUrl = document.location.href.match(/(^[^#]*)/)[0];
			var hash = '#' + actionName;
			if (objectName) {
				hash += '/' + objectName + '/' + objectUid;
			}
			window.history.pushState(null, '', currentUrl + hash);
		}
	},

	callHashRequestedAction: function() {
		// If a specific action is requested, load it
		// hash might be: "actionName" or "actionName/objectName/objectUid"
		var hashData = document.location.hash.substr(1).split('/');
		if (hashData.length > 1) {
			// ajax object action
			var allowedActions = ['edit', 'editVotings'];
			if ($.inArray(hashData[0], allowedActions) !== -1) {
				var actionName = hashData[0];
				var objectName = hashData[1];
				var objectUid = hashData[2];
				EasyvoteEducation.performAjaxObjectAction(actionName, objectName, objectUid)
			} else {
				// disallowed action, fall back to dashboard
				EasyvoteEducation.loadAction('dashboard');
			}
		} else {
			// action without an object involved
			// hashData[0] --> actionName
			EasyvoteEducation.loadAction(hashData[0])
		}
	}

};