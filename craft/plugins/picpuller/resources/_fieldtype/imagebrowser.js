// console.log('Pic Puller JS loaded.');
(function( $ ) {


    $.fn.imagebrowser = function() {
		var picPullerModal;
		var theButton = $(this.selector + '-field .btn');
		var inputTextField = $("#" + PicPuller.fieldId + " input");
		var previewField = $("#" + PicPuller.fieldId + " .igpp.preview");
		var lookupBt = $("#" + PicPuller.fieldId + " .igpp.lookup");
		var deleteBt = $("#" + PicPuller.fieldId + " .igpp.delete");
		// PicPuller.adminPath is set in the /craft/plugins/picpuller/fieldtypes/PicPuller_ImageBrowserFieldType.php file
		// console.log('PicPuller.adminPath:', PicPuller.adminPath);
		// console.log('PicPuller.userId:', PicPuller.userId);
		// console.log('PicPuller.fieldId:', PicPuller.fieldId);
		// console.log('inputTextField:', inputTextField);

		function doLookup() {
			if (inputTextField.val() !== '') {
				// console.log('There is text present in the PP field.');
				var inputTextFieldVal = inputTextField.val();
				if ( inputTextFieldVal.indexOf('instagr') === 7 ) {
					translateURLtoMediaID(_trim11(inputTextFieldVal));
				} else {
					loadMediaById(inputTextFieldVal);
				}
			}
		}

		doLookup();

		theButton.on('click', function () {
			var ppBrowserType = theButton.data('ppbrowsertype');
			// console.log('ppBrowserType', ppBrowserType);

			// console.log('$(this) button clicked', $(this));

			var targetFieldForId = $(this).parent().find('input');
			// console.log('targetFieldForId', targetFieldForId);
			// console.log('inputTextField', inputTextField);
			// These 2 values indicate to me that since thare are the same,
			// the targeting of the input field isn't the problem
			// Instead, something is messing up the ability of subsequently
			// created Modal windows from receiving click events.


			var divPart1 = '<div class="modal elementselectormodal" id="pp-thumbs"><div class="body"><div class="content" ><div class="main"><div class="toolbar"><h3>Instagram Images</h3>';
			var divPart2 = '<div class="pp-search"><input type="text" placeholder="one tag only"/> <div class="btn disabled submit small">Search</div></div>';
			if (ppBrowserType == 0) {
				// the 0 value means there should be no search button on this modal window
				// so just empty this string
				divPart2 = '';
			}
			var divPart3 = '</div><div class="elements"></div></div></div></div><div class="footer"><div class="buttons rightalign"><div class="btn cancel" tabindex=0>Cancel</div></div></div></div>';

			var $div = $(divPart1+divPart2+divPart3);
			if (!picPullerModal) {
				//console.warn('No picPullerModal exits for this browse button');
				picPullerModal = new Garnish.Modal($div, {
					resizable: true //,
					// onFadeIn: function () {
					// 	//console.log('Modal is faded in.');
					// },
					// onShow: function() {
					// 	//console.log('I\'m showing');
					// }
				});
				addModelButtonActions(picPullerModal);
				// if the ppBrowserType is 1, we don't show the user's media feed
				// otherwise we do the loadImages on initial creation of the modal window
				if (ppBrowserType !== 1) {
					loadImages();
				}
			} else {
				//console.log('picPullerModal existed already - just show it');
				picPullerModal.show();
			}
		})

		lookupBt.on('click', doLookup);

		deleteBt.on('click', function() {
			inputTextField.val('');
			closePreview();
		});

		function closePreview() {
			previewField.slideUp().html('');
		}

		inputTextField.keyup(function(e) {
			_checkForValueinPPfield();
			closePreview();
		});


		function addModelButtonActions(ppmodalwindow) {
			var thisModal = $(ppmodalwindow.$container[0]);
			// console.log('thisModal ', thisModal);
			var thisSearchBt = thisModal.find('.pp-search .btn');
			thisModal.on('click', '.btn', function(e) {
				if( $(e.target).hasClass('cancel') ) {
					picPullerModal.hide();
				};
				if( $(e.target).hasClass('igpic_next') ) {
					if ( $(e.target).hasClass('type-media') ) {
						$(e.target).removeClass('btn').removeClass('igpic_next').addClass('next_loading');
						loadImages($(e.target).data('nextmaxid'));
					}
					else if ( $(e.target).hasClass('type-search') ) {
						$(e.target).removeClass('btn').removeClass('igpic_next').addClass('next_loading');
						loadImagesByTag(PicPuller.searchTag, $(e.target).data('nextmaxid'));
					}

				};
			});
			thisModal.on('click', '.selectable', function(e) {
				var selectedMediaId = $(e.currentTarget).data('mediaid');
				inputTextField.val(selectedMediaId);
				loadMediaById(selectedMediaId);
				picPullerModal.hide();
			});
			thisModal.on('click', '.pp-search .btn', function (e) {
				if ( !thisSearchBt.hasClass('disabled') ) {
					// saving the search tag in the PicPuller global for reuse in the
					// "more" images button link
					PicPuller.searchTag = $(this).prev().val();

					// clear old thumbs since this is a new search term
					var thumbField = $('#pp-thumbs .main .elements');
					thumbField.empty();

					loadImagesByTag(PicPuller.searchTag);

				} else {
					// console.log('no search terms');
					// Since search was disabled, this shouldn't be possible though.
				}
			})
			thisModal.find('.pp-search input').keyup(function(e) {
				if($(this).val() !== '') {
					thisSearchBt.removeClass('disabled');
				} else {
					thisSearchBt.addClass('disabled');
				}
			});
		}

		function loadImages(nextMaxId) {
			var nextMaxId = (nextMaxId === undefined) ? '' : nextMaxId;
			var theURL = "/" + PicPuller.adminPath + "/picpuller/mediarecent/" + nextMaxId;
			// var theThumbTarget = $('#'+PicPuller.fieldId);
			$.ajax({
				url: theURL,
				dataType: 'json',
				success: function(data) {
					$('.igpic_next').remove();
					$('.type-media').remove();
					$('.next_loading').remove();
					var thumbField = $('#pp-thumbs .main .elements');
					var loadMore = '<div class="igpic igpic_end">' +'END' + '</div>';
					if (data.meta.nextMaxId != '') {
						loadMore = '<div class=" igpic igpic_next type-media btn" data-nextmaxid="'+data.meta.nextMaxId +'"></div>';
					}
					for(var i = 0; i < data.ppimages.length; i++){
						var mediatype = data.ppimages[i].video ? 'video' : 'photo';
						var pic = '<div class="igpic selectable" data-mediaid="'+data.ppimages[i].media_id +'"><div class="'+mediatype+'"></div><img src="'+data.ppimages[i].url+'" alt="" width="100" height="100" border="0" /> </div>';
						thumbField.append(pic);
					}
					thumbField.append(loadMore);
				}
			});
		}

		function loadMediaById(mediaId) {
			var theURL = "/" + PicPuller.adminPath + "/picpuller/mediabyid/" + mediaId;
			// console.log(theURL);
			$.ajax({
				url: theURL,
				dataType: 'json',
				success: function(data) {
					//console.log('success', data);
					var isVideo = data.ppimages[0].video;
					var thePreview = "<div><div class='igpic_image'><img src='" + data.ppimages[0].url +"' alt='" + data.ppimages[0].caption +"' width=100 height=100 border=0 /></div><div class='igpic_info'><p class='titlefield'>Preview from Instagram</p><p class='caption'><a href='" + data.ppimages[0].link +"' target='_blank' title='Open image link in new window'>" + data.ppimages[0].caption +"</a></p><p class='igpic_author'>By <strong>" + data.ppimages[0].full_name +"</strong> @" + data.ppimages[0].username +"</p></div></div>";
					previewField.html(thePreview);
					previewField.slideDown();
				},
				error: function(data) {
					// console.log('error', data);
				}
			});
		}

		function loadImagesByTag(tag, nextMaxId) {
			var nextMaxId = (nextMaxId === undefined) ? '' : nextMaxId;
			var tag = encodeURIComponent(_trim11(tag));
			var theURL = "/" + PicPuller.adminPath + "/picpuller/mediabytag/" + tag + "/" + nextMaxId;

			// console.log(theURL);
			// var theThumbTarget = $('#'+PicPuller.fieldId);
			$.ajax({
				url: theURL,
				dataType: 'json',
				success: function(data) {
					// console.log("SEACH DATA RECEIVED");
					$('.igpic_next').remove();
					$('.type-search').remove();
					$('.next_loading').remove();
					var thumbField = $('#pp-thumbs .main .elements');
					var loadMore = '<div class="igpic igpic_end">' +'END' + '</div>';
					if (data.meta.nextMaxId != '') {
						loadMore = '<div class=" igpic igpic_next type-search btn" data-nextmaxid="'+data.meta.nextMaxId +'"></div>';
					}
					for(var i = 0; i < data.ppimages.length; i++){
						var mediatype = data.ppimages[i].video ? 'video' : 'photo';
						var pic = '<div class="igpic selectable" data-mediaid="'+data.ppimages[i].media_id +'"><div class="'+mediatype+'"></div><img src="'+data.ppimages[i].url+'" alt="" width="100" height="100" border="0" /> </div>';
						thumbField.append(pic);
					}
					thumbField.append(loadMore);
				}
			});
		}

		function translateURLtoMediaID(url) {
			var media_id = null;
			$.ajax({
					url: "http://api.instagram.com/oembed?url="+url,
					contentType: 'text/plain',
					xhrFields: {
						withCredentials: false
					},
					dataType: 'jsonp',
					success: function(data) {
						// console.log('Data received from Instagram oembed.');
						// console.log(data);
						// console.log(data.media_id);
						if (data.media_id){
							media_id = data.media_id;
							inputTextField.val(media_id);
							loadMediaById(media_id);
						} else {
							return null;
						}
					}
				});
		}

		// helper trim function
		function _trim11 (str) {
			str = str.replace(/^\s+/, '');
			for (var i = str.length - 1; i >= 0; i--) {
				if (/\S/.test(str.charAt(i))) {
					str = str.substring(0, i + 1);
				break;
				}
			}
			return str;
		}

		function _checkForValueinPPfield() {
			var myValue= inputTextField.val();
			if(myValue !== '') {
				lookupBt.removeClass('hidden');
				return true;
			} else {
				lookupBt.addClass('hidden');
				return false;
			}
		}

        return this;
    };

}( jQuery ));