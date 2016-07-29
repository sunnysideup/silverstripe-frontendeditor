jQuery(document).ready(
	function(){
		//must be first ...
		FrontEndEditForm.init();
	}
);


var FrontEndEditForm = {

	debug: false,

	formSelector: "#FrontEndEditForm_Form",

	timer: null,

	itsAclick: false,

	ajaxForOptionsNewRecord: {
		target:        '#FontEndEditorFormOuter',   // target element(s) to be updated with server response
		beforeSubmit:  function(){}, //FrontEndEditForm.showRequest,  // pre-submit callback
		success:       function(){
			FrontEndEditForm.changedData = false;
		}, //FrontEndEditForm.showResponse  // post-submit callback

		// other available options:
		//url:       url         // override for form's 'action' attribute
		//type:      type        // 'get' or 'post', override for form's 'method' attribute
		//dataType:  null        // 'xml', 'script', or 'json' (expected server response type)
		//clearForm: true        // clear all form fields after successful submit
		//resetForm: true        // reset the form after successful submit

		// $.ajax options can be used here too, for example:
		//timeout:   3000
	},

	ajaxForOptionsExistingRecord: {
		//target:        '#FontEndEditorFormOuter',   // target element(s) to be updated with server response
		beforeSubmit:  function(){}, //FrontEndEditForm.showRequest,  // pre-submit callback
		success:       function(){
			FrontEndEditForm.changedData = false;
		}, //FrontEndEditForm.showResponse  // post-submit callback

		// other available options:
		//url:       url         // override for form's 'action' attribute
		//type:      type        // 'get' or 'post', override for form's 'method' attribute
		//dataType:  null        // 'xml', 'script', or 'json' (expected server response type)
		//clearForm: true        // clear all form fields after successful submit
		//resetForm: true        // reset the form after successful submit

		// $.ajax options can be used here too, for example:
		//timeout:   3000
	},

	changedData: false,

	deleteLinkSelectors: ".frontEndRemoveLink",

	init: function(){
		//other stuff
		FrontEndEditForm.coreInits();
		FrontEndEditForm.checkCookies();
		FrontEndEditForm.dontTabOnAlsoEdit();
		FrontEndEditForm.initRemoveLink();
		FrontEndEditForm.checkHowCompleteFormIs();
		FrontEndEditForm.progressBarListeners();
		FrontEndEditForm.deleteConfirmation();
	},

	coreInits: function(){

		//mark data as changed after an update.
		jQuery(FrontEndEditForm.formSelector).on(
			"change",
			FrontEndEditForm.formSelector+ " input, "+FrontEndEditForm.formSelector+ " select, "+FrontEndEditForm.formSelector+ " textarea",
			function(){
				if(FrontEndEditForm.debug) {console.debug("data has changed");}
				FrontEndEditForm.changedData = true;
				FrontEndEditForm.checkHowCompleteFormIs();
			}
		)

		//save when clicking on a link or an h3
		jQuery(this.formSelector).on(
			"click",
			"h3, a",
			function(){
				if(FrontEndEditForm.debug) {console.debug("doing the click");}
				if(FrontEndEditForm.changedData) {
					FrontEndEditForm.changedData = false;
					if(FrontEndEditForm.isNewRecord()) {
						//specialOptions = xxx
						//specialOptions.target = "boo"
						//jQuery(FrontEndEditForm.formSelector).ajaxSubmit(FrontEndEditForm.ajaxForOptionsNewRecord);
					}
					else {
						if(FrontEndEditForm.debug) {console.debug("saving data now");}
						jQuery(FrontEndEditForm.formSelector).ajaxSubmit(FrontEndEditForm.ajaxForOptionsExistingRecord);
					}
				}
			}
		);

		//add click functionality on H3...
		jQuery("h3")
			.not("doNotSlide")
			.attr("tabIndex", 0)
			.focus(function(event){
				if(FrontEndEditForm.itsAclick) {
					//do nothing because the click will do the work ...
				}
				else {
					jQuery(this).click();
				}
			})
			.on(
				'click',
				function() {
					FrontEndEditForm.itsAclick = false;
					if(!jQuery(this).hasClass('doNotSlide')){
						jQuery(this)
							.toggleClass("closed")
							.toggleClass("opened");
						if(jQuery(this).hasClass('closed')) {
							jQuery(this)
								.nextUntil( "h3, .CompositeField" )
								.removeClass("openedField")
								.addClass("closedField");
						}
						else {
							jQuery(this)
								.nextUntil( "h3, .CompositeField" )
								.removeClass("closedField")
								.addClass("openedField");
								document.cookie = "current_section_heading_" + FrontEndEditForm.uniquePageID() + "=" + jQuery(this).attr('id');
							jQuery('h3.opened').not(this).click();
						}
					}
				}
			)
			.on(
				"mousedown",
				function() {
					FrontEndEditForm.itsAclick = true;
				}
			);

		//set default open / close...
		jQuery(FrontEndEditForm.formSelector+' h3').each(
			function(i, el){
				if(i == 0) {
					jQuery(el).addClass("opened");
				}
				else {
					jQuery(el).addClass("opened").click();
				}
			}
		);

		//on submit, always show the first one as this may have the required fields in it...
		jQuery(FrontEndEditForm.formSelector + " input[type='submit']").click(
			function(){
				var item = jQuery(FrontEndEditForm.formSelector+' h3').first();
				if(jQuery(item).hasClass("closed")) {
					jQuery(item).click();
				}
			}
		);
		if(FrontEndEditForm.debug) {console.debug("=========== form ready to go with ID:"+this.IDforRecord()+" and classname: "+this.classNameForRecord());}
	},

	checkCookies: function(){
		var cookie = "current_section_heading_" + this.uniquePageID();
		var id = FrontEndEditForm.getCookie(cookie);
		if(id) {
			jQuery(FrontEndEditForm.formSelector+' h3.opened').click();
			if(jQuery("h3#" + id).length > 0) {
				jQuery("h3#" + id).click();
			}
		}
	},

	dontTabOnAlsoEdit: function(){
		jQuery(".modalPopUp").attr("tabIndex", -1);
	},

	getCookie: function(cname) {
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for(var i=0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length,c.length);
			}
		}
		return "";
	},

	openMySection: function(){
		if(jQuery(this).is(":visible")) {
			//do nothing;
			console.debug("visible");
		}
		else {
			console.debug("not visible");
			var previousH3 = jQuery(this).prev("h3:first");
			if(jQuery(previousH3).hasClass("closed")) {
				jQuery(previousH3).click();
			}
		}
	},

	initRemoveLink: function(){
		jQuery("body").on(
			"click",
			FrontEndEditForm.deleteLinkSelectors,
			function(event) {
				event.preventDefault();
				var el = this;
				var link = jQuery(el).attr("href");
				jQuery(el).parent().addClass("removingNow").fadeOut();
				jQuery.get(
					link,
					function() {
						jQuery(el).parent().hide();
						jQuery(el).removeClass("removingNow");
					}
				);
				return false;
			}
		);
	},

	checkHowCompleteFormIs: function(buildList){
		var fieldCount = 0;
		var fieldsCompleted = 0;
		var needsCompletion = false;
		jQuery(FrontEndEditForm.formSelector+ " div.field").each(
			function(i, el) {
				if(!jQuery(this).hasClass("readonly")){
					var element = jQuery(el);
					var input = jQuery(el).find("input, textarea, select");
					var inputID = input.attr("id");
					var heading = element.prevAll('h3:first').text();
					var headingID = element.prevAll('h3:first').attr("id") + "_NeedsCompletion";
					if(input.hasClass("checkbox")){
						var label = element.find("label").text();
						if(input.attr('checked')){
							jQuery('#' + inputID + 'NeedsCompletion').remove();
							needsCompletion = false;
							fieldsCompleted++;
						}
						else{
							needsCompletion = true;
						}
					}
					else {
						var label = element.find("label.left").text();
						var val = input.val();
						if(typeof val == "undefined") {
							alert(label);
						}
						if(val.length > 0 && val !== "0" && val !== "0.00"){
							jQuery('#' + inputID + 'NeedsCompletion').remove();
							needsCompletion = false;
							fieldsCompleted++;
						}
						else{
							needsCompletion = true;
						}
					}
					if(needsCompletion && buildList){
						if (jQuery('#' + headingID).length == 0){
							jQuery("#FieldsToCompleteList").append('<dt id="' + headingID +'">' + heading + '</dt>');
						}
						jQuery("#FieldsToCompleteList").append('<dd id="' + inputID +'NeedsCompletion">' + label + '</dd>');
					}
					fieldCount++;
				}
			}
		);
		var completionRate = Math.round(((fieldsCompleted/fieldCount ) * 100));
		if(completionRate == 0){
			jQuery("#ProgressBar").hide();
		}
		else{
			jQuery("#ProgressBar").show();
			jQuery("#CompletionPercentage").animate({width: completionRate + "%"}, 500);
		}
	},

	progressBarListeners: function(){
		jQuery("#ProgressBar").on(
			'click',
			function(){
				var listItems = jQuery("#FieldsToCompleteList dd");
				if(listItems.length == 0){
					FrontEndEditForm.checkHowCompleteFormIs(true);
				}
				jQuery("#FieldsToCompleteList").slideToggle(400,
					function () {
						if(listItems.length > 0){
							jQuery('#FieldsToCompleteList').empty();
						}
					}
				);
			}
		);
		jQuery(document).on(
			'click',
			'#FieldsToCompleteList dt',
			function(){
				var headingID = jQuery(this).attr("id").replace("_NeedsCompletion", "");
				jQuery("#" + headingID).trigger("click");
			}
		);
	},

	deleteConfirmation: function(){
		jQuery("input[name='action_deleterecord']").on(
			"click",
			function(event) {
				if (!confirm('Are you sure you want to delete this record?')) {
					event.preventDefault();
				}
			}
		);
	},

	_uniquePageID: "",

	uniquePageID: function(){
		if(this._uniquePageID === "") {
			this._uniquePageID = this.classNameForRecord() + "_" + this.IDforRecord();
		}
		return this._uniquePageID;
	},

	_IDforRecord: 0,

	IDforRecord: function(){
		if(this._IDforRecord === 0) {
			this._IDforRecord = jQuery("input[name='IDToUse']").attr("value");
		}
		return this._IDforRecord;
	},

	_classNameForRecord: 0,

	classNameForRecord: function(){
		if(this._classNameForRecord === 0) {
			this._classNameForRecord = jQuery("input[name='ClassNameToUse']").attr("value");
		}
		return this._classNameForRecord;
	},

	isNewRecord: function(){
		return this.IDforRecord() == 0 ? true : false;
	}

}
