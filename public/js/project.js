
var isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};

function debug(msg, debugOn)
{
	if (debugOn)
		console.log(msg);
}

function d(msg)
{
	console.log(msg);
}

function clipboardCopy(event, idFlash, id)
{
	event.preventDefault();

	var text = document.getElementById(id).innerHTML;

	// create an input field that can be selected
	var target = document.createElement("textarea");
	target.style.position = "absolute"; // keep it off of the screen
	target.style.left = "-9999px";
    target.style.top = "0";
    target.id = "_hiddenCopyText_";
	target.setAttribute('readonly', ''); // keeps focus from going to it
    document.body.appendChild(target); // add it to the page

	// do the flash affect (only included in full jquery / we are using jquery slim for the moment
//	$("#" + idFlash + ' p').fadeTo('fast', 0.1).fadeTo('slow', 1.0);
//	$("#" + idFlash).fadeTo('fast', 0.1).fadeTo('slow', 1.0);
	$("#" + idFlash).css("color", "red");
    $("#status").text("copied");

	// remove the <br>'s and <p>'s and <span>'s
	text = text.replace(/(\r\n|\n|\r)/gm, "");
    text = text.trim().replace(/<br\/>/gi, "\n");
    text = text.trim().replace(/<br \/>/gi, "\n");
    text = text.trim().replace(/<br>/gi, "\n");
    text = text.trim().replace(/<p>/gi, "\n");
    text = text.trim().replace(/<\/p>/gi, "\n");

    text = text.trim().replace(/<span style="color:green;">/gi, "");
    text = text.trim().replace(/<\/span>/gi, "");

	// put the stripped text into the hidden field and select it
    target.textContent = text;
	target.select();

    // copy the selection
    var succeed;
    try {
    	  succeed = document.execCommand("copy");
		  //alert('success');
    } catch(e) {
        succeed = false;
		alert('error copying text');
	}

	// remove the temporary input field
	document.body.removeChild(target);
}

function clipboardCopyText(event, idFlash, id)
{
	event.preventDefault();

	// do the flash affect
	$("#" + idFlash + ' p').fadeTo('fast', 0.1).fadeTo('slow', 1.0);
	$("#" + idFlash).fadeTo('fast', 0.1).fadeTo('slow', 1.0);

	document.getElementById(id).select();

    // copy the selection
    var succeed;
    try
	{
		succeed = document.execCommand("copy");
    }
	catch(e)
	{
        succeed = false;
	}
}

function save()
{
	$( "#save" ).click();
}

function urlEncode(fromId, toId)
{
    var fromElem = document.getElementById(fromId);
	var toElem = document.getElementById(toId);
	if (fromElem && toElem)
	{
		text = convertAccentChars(fromElem.value);
		toElem.value = encodeURI(text.replace(/[\W_]+/g, "-").toLowerCase());
	}
	else
	{
		alert('Error creating permalink');
	}
}

function convertAccentChars(text)
{
	//
	// replace accent / special characters one by one
	//
	text = text.replace(/ñ/g, "n");
	text = text.replace(/ç/g, "c");
	text = text.replace(/[ÀÁÄÂàáâäã]+/g, "a");
	text = text.replace(/[ÉÈËÊèéêë]+/g, "e");
	text = text.replace(/[ÍÌÏÎìíîï]+/g, "i");
	text = text.replace(/[ÓÒÖÔòóôöõø]+/g, "o");
	text = text.replace(/[ÙÚÜÛùúûü]+/g, "u");
	text = text.replace(/Ÿÿ/g, "y");
	
	return text;
}

function urlEncodeWithDate(fromId, fromYearId, fromMonthId, fromDayId, toId)
{
    var fromElem = document.getElementById(fromId);
    var fromDay = document.getElementById(fromDayId);
    var fromMonth = document.getElementById(fromMonthId);
    var fromYear = document.getElementById(fromYearId);
	var toElem = document.getElementById(toId);
	if (fromElem && toElem && fromDay && fromMonth && fromYear)
	{
		toElem.value = encodeURI(fromElem.value.replace(/[\W_]+/g, "-").toLowerCase());

		if (fromYear.value > 0 && fromMonth.value > 0 && fromDay.value > 0)
		{
			toElem.value += '-' + fromYear.value + '-' + pad(fromMonth.value, 2) + '-' + pad(fromDay.value, 2);
		}
	}
	else
	{
		alert('Error creating permalink');
	}
}

function createPhotoName(fromId, fromLocationId, toId)
{
    var fromElem = document.getElementById(fromId);
    var fromLocation = document.getElementById(fromLocationId);
	var toElem = document.getElementById(toId);
	if (fromElem && toElem && fromLocation)
	{
		var location = fromLocation.value.trim().split(", ");
		if (location.length == 1)
		{
			location = fromLocation.value.trim().split(",");

			if (location.length == 1)
			{
				location = fromLocation.value.trim().split(" ");
			}
		}

		if (location.length <= 1) // if nothing to split then there is one empty array element
		{
			// nothing to do
			toElem.value = '';
		}
		else if (location.length > 2)
		{
			// flip the words, for example "Beijing, China" becomes "china-beijing"
			toElem.value = location[2] + '-';
			toElem.value += location[1] + '-';
			toElem.value += location[0] + '-';
		}
		else if (location.length > 1)
		{
			// flip the words, for example "Beijing, China" becomes "china-beijing"
			toElem.value = location[1] + '-';
			toElem.value += location[0] + '-';
		}
		else
		{
			// not sure of the format, just copy it as is
			toElem.value = fromLocation.value.trim() + '-';
		}

		// trim the trash
		toElem.value += fromElem.value.trim();

		// remove apostrophes
		toElem.value = toElem.value.replace(/\'/g, "");

		// replace & with n, B&B to BnB
		toElem.value = toElem.value.replace(/\&/g, "n");

		//
		// replace accent / special characters one by one
		//
		toElem.value = toElem.value.replace(/ñ/g, "n");
		toElem.value = toElem.value.replace(/ç/g, "c");
		toElem.value = toElem.value.replace(/[ÀÁÄÂàáâäã]+/g, "a");
		toElem.value = toElem.value.replace(/[ÉÈËÊèéêë]+/g, "e");
		toElem.value = toElem.value.replace(/[ÍÌÏÎìíîï]+/g, "i");
		toElem.value = toElem.value.replace(/[ÓÒÖÔòóôöõ]+/g, "o");
		toElem.value = toElem.value.replace(/[ÙÚÜÛùúûü]+/g, "u");
		toElem.value = toElem.value.replace(/Ÿÿ/g, "y");

		// replace whitespace with '-' and make all lower
		toElem.value = encodeURI(toElem.value.replace(/[\W_]+/g, "-").toLowerCase());

		// check for and skip repeating words
		var words = toElem.value.split("-");
		toElem.value = "";
		var prev = "";
		words.forEach(
			function(element) {
				if (element != prev)
				{
					if (toElem.value.length > 0)
						toElem.value += "-";

					toElem.value += element;
				}

				prev = element;
		});

		// remove trailing dash, if any
		if (toElem.value.endsWith("-"))
			toElem.value = toElem.value.slice(0, -1);
	}
	else
	{
		alert('Error creating photo file name');
	}
}

function pad(number, length)
{
    var str = '' + number;

    while (str.length < length) {
        str = '0' + str;
    }

    return str;
}

function changeDate(inc, fromYearId, fromMonthId, fromDayId, useCurrentDate = false)
{
    var fromDay = document.getElementById(fromDayId);
	if (fromDay && parseInt(fromDay.value) == 0)
	{
		if (useCurrentDate)
		{
			var today = new Date();
			fromDay.value = today.getDate();		}
		else
		{
			fromDay = null;
		}
	}

    var fromMonth = document.getElementById(fromMonthId);
    var fromYear = document.getElementById(fromYearId);

//	var lastDayOfTheMonth = getLastDayOfMonth(parseInt(fromMonth.value));

	if (fromDay && fromMonth && fromYear) // using month/day/year
	{
		if (inc == 0) // this means clear the date
		{
			fromDay.value = 0;
			fromMonth.value = 0;
			fromYear.value = 0;
		}
		else if (inc == 99) // this means set to current day
		{
			var today = new Date();

			fromDay.value = today.getDate();
			fromMonth.value = today.getMonth() + 1;
			fromYear.value = today.getFullYear();
		}
		else
		{
			var newDate = parseInt(fromDay.value) + inc;

			// get last day of current month
			var lastDayOfMonth = new Date(parseInt(fromYear.value), parseInt(fromMonth.value), 0).getDate();

			if (newDate == 0) // roll to previous month
			{
				newMonth = parseInt(fromMonth.value) - 1;
				if (newMonth >= 0)
				{
					fromMonth.value = newMonth;
				}
				else
				{
					// roll around to previous year
					fromMonth.value = 12;
					fromYear.value = parseInt(fromYear.value) - 1;
				}

				// get last day of new month
				lastDayOfMonth = new Date(parseInt(fromYear.value), parseInt(fromMonth.value), 0).getDate();
				fromDay.value = lastDayOfMonth;
			}
			else if (newDate > lastDayOfMonth) // roll to next month
			{
				fromDay.value = 1;
				newMonth = parseInt(fromMonth.value) + 1;

				if (newMonth <= 12)
				{
					fromMonth.value = newMonth;
				}
				else
				{
					// roll over to next year
					fromMonth.value = 1;
					fromYear.value = parseInt(fromYear.value) + 1;
				}
			}
			else
			{
				fromDay.value = newDate;
			}
		}
	}
	else if (fromMonth && fromYear) // using month/year only so attempt to switch months
	{
		if (inc == 0) // this means clear the date
		{
			fromMonth.value = 0;
			fromYear.value = 0;
		}
		else if (inc == 99) // this means set to current day
		{
			var today = new Date();

			fromMonth.value = today.getMonth() + 1;
			fromYear.value = today.getFullYear();
		}
		else
		{
			var newDate = parseInt(fromMonth.value) + inc;

			if (newDate == 0) // roll to previous year
			{
				fromMonth.value = 12;
				fromYear.value = parseInt(fromYear.value) - 1;
			}
			else if (newDate > 12) // roll to next year
			{
				fromMonth.value = 1;
				fromYear.value = parseInt(fromYear.value) + 1;
			}
			else
			{
				// just change month
				fromMonth.value = newDate;
			}
		}
	}
	else
	{
		alert('Error changing dates');
	}
}

function changeDateNEW(inc, fromYearId, fromMonthId, fromDayId, useCurrentDate = false)
{
	var monthDays = [31,28,31,30,31,30,31,31,30,31,30,31];

	var fromDay = document.getElementById(fromDayId);
    var fromMonth = document.getElementById(fromMonthId);
    var fromYear = document.getElementById(fromYearId);

	if (inc == 0) // this means clear the date
	{
		fromDay.value = 0;
		fromMonth.value = 0;
//		fromYear.value = 0;

		return;
	}
	else if (inc == 99) // this means set to current day
	{
		var today = new Date();

		fromDay.value = today.getDate();
		fromMonth.value = today.getMonth() + 1;
		fromYear.value = today.getFullYear();

		return;
	}

 	if (fromDay && parseInt(fromDay.value) == 0)
	{
		if (useCurrentDate)
		{
			var today = new Date();
			fromDay.value = today.getDate();		}
		else
		{
			fromDay = null;
		}
	}

	if (fromMonth && parseInt(fromMonth.value) == 0)
	{
		fromMonth = null;
	}

//	var lastDayOfTheMonth = getLastDayOfMonth(parseInt(fromMonth.value));
//alert(fromDay.value + ", " + fromMonth.value + ", " + fromYear.value);

	if (fromDay && fromMonth && fromYear) // using month/day/year
	{
			var newDate = parseInt(fromDay.value) + inc;

			// get last day of current month
			var lastDayOfMonth = new Date(parseInt(fromYear.value), parseInt(fromMonth.value), 0).getDate();

			if (newDate == 0) // roll to previous month
			{
				newMonth = parseInt(fromMonth.value) - 1;
				if (newMonth >= 0)
				{
					fromMonth.value = newMonth;
				}
				else
				{
					// roll around to previous year
					fromMonth.value = 12;
					fromYear.value = parseInt(fromYear.value) - 1;
				}

				// get last day of new month
				lastDayOfMonth = new Date(parseInt(fromYear.value), parseInt(fromMonth.value), 0).getDate();
				fromDay.value = lastDayOfMonth;
			}
			else if (newDate > lastDayOfMonth) // roll to next month
			{
				fromDay.value = 1;
				newMonth = parseInt(fromMonth.value) + 1;

				if (newMonth <= 12)
				{
					fromMonth.value = newMonth;
				}
				else
				{
					// roll over to next year
					fromMonth.value = 1;
					fromYear.value = parseInt(fromYear.value) + 1;
				}
			}
			else
			{
				fromDay.value = newDate;
			}
	}
	else if (fromMonth && fromYear) // using month/year only so attempt to switch months
	{
			var newDate = parseInt(fromMonth.value) + inc;

			if (newDate == 0) // roll to previous year
			{
				fromMonth.value = 12;
				fromYear.value = parseInt(fromYear.value) - 1;
			}
			else if (newDate > 12) // roll to next year
			{
				fromMonth.value = 1;
				fromYear.value = parseInt(fromYear.value) + 1;
			}
			else
			{
				// just change month
				fromMonth.value = newDate;
			}
	}
	else if (fromYear) // only using year so loop years
	{
		//alert(fromYear.value);

			var newDate = parseInt(fromYear.value) + inc;

			if (newDate < 2010) // roll to previous year
			{
				fromYear.value = 2020;
			}
			else if (newDate > 2020) // roll to next year
			{
				fromYear.value = 2010;
			}
			else
			{
				// just change year
				fromYear.value = newDate;
			}
	}
	else
	{
		alert('Error changing dates');
	}
}



function decodeHtml(html)
{
	var txt = document.createElement("textarea");
	txt.innerHTML = html;
	return txt.value;
}

function popup(id, filename, photo_id)
{
	var origImg = document.getElementById(photo_id);
	title = decodeHtml(origImg.title);

	var popupDiv = document.getElementById("myModal");
	popupDiv.style.display = "block";

	var popupImg = document.getElementById("popupImg");
	popupImg.src = "/img/entries/" + id + "/" + filename;
	popupImg.title = title;

	var popupImgTitle = document.getElementById("popupImgTitle");
	popupImgTitle.innerHTML = title;
}

function nextPhoto(found)
{
	var popupImg = null;
	var photos = document.getElementsByClassName("popupPhotos");
	var popupImg = document.getElementById("popupImg");
	var popupImgTitle = document.getElementById("popupImgTitle");

	for(var i = 0; i < photos.length; i++)
	{
		if (found)
		{
			popupImg.src = photos.item(i).src;
			popupImg.title = decodeHtml(photos.item(i).title);
			popupImgTitle.innerHTML = popupImg.title;
			return;
		}

		// if it's the current photo and then set the found flag to stop at the
		// next photo at the top of the next iterartion
		var count = i + 1; // if it's the last item don't consider it found so we can wrap to the first item
		if (count < photos.length && popupImg.src == photos.item(i).src)
		{
			found = true;
		}
	}

	if (!found)
	{
		// show the first photo
		nextPhoto(true);
	}
}

function popdown()
{
	var popupDiv = document.getElementById("myModal");
	popupDiv.style.display = "none";
}

function showAllRows(tableId, showAllButtonId)
{
	var showAllButton = document.getElementById(showAllButtonId);
	showAllButton.style.display = "none";

	var rows = document.getElementById(tableId).rows;

	for(var i = 0; i < rows.length; i++)
	{
		rows[i].style.display = "block";
		//alert(rows[i].style.display);
	}
}

function onCategoryChange(id)
{
	var xhttp = new XMLHttpRequest();
	var url = '/categories/subcategories/' + id;

	xhttp.onreadystatechange = function()
	{
		//alert(this.status);

		if (this.status == 200)
		{
			//alert(this.responseText);
		}
		else if (this.status == 404)
		{
			alert(this.responseText);
		}

		if (this.readyState == 4 && this.status == 200)
		{
			/*
			alert(
				'call response: ' + this.responseText +
				', length: ' + this.responseText.length
				+ ', char: ' + this.responseText.charCodeAt(0)
				+ ' ' + this.responseText.charCodeAt(1)
			);
			*/

			//
			// results
			//
			//alert(this.requestText);

			// get the select element
			var s = document.getElementById("subcategory_id");

			// replace the option list
			s.innerHTML = this.responseText;
		}
	};

	xhttp.open("GET", url, true);
	xhttp.send();
}

function ajaxexec(url, resultsId = '', resultsInput = false, resultsCallback = null)
{
	var xhttp = new XMLHttpRequest();
	var debugOn = false;

	debug('ajaxexec: url: ' + url, debugOn);
	//debug('ajaxexec: resultsId: ' + resultsId, debugOn);
	
	xhttp.onreadystatechange = function()
	{
		//alert(this.status);

		if (this.status == 404) // page not found?
		{
			debug('ajaxexec: 404', debugOn);
			
			if (resultsId.length > 0)
				$(resultsId).text('Server Error 404');
		}

		if (this.readyState == 4)
		{
			if (this.status == 200)
			{
				//
				// results
				//
				if (resultsId.length > 0)
				{
					//$(resultsId).text('definition: ' + this.responseText);
					if (this.responseText.startsWith('<'))
					{
						debug('ajaxexec: html returned', debugOn);						
						$(resultsId).html(this.responseText);
					}
					else if (resultsInput) // put results in an input
					{
						debug('ajaxexec: text for input returned', debugOn);
						$(resultsId).val(this.responseText);
					}
					else
					{
						debug('ajaxexec: text returned', debugOn);
						$(resultsId).text(this.responseText);
						$(resultsId).css('color', '#a37800');
					}
				}
				else
				{
					debug('ajaxexec: results empty', debugOn);
				}
				
				if (resultsCallback != null)
					resultsCallback(this.responseText);
				
				//debug(this.responseText);
			}
			else
			{
				debug('ajaxexec: 500');
				
				if (resultsId.length > 0)
					$(resultsId).text('Server Error ' + this.status);
			}
		}
	};

	xhttp.open("GET", url, true);
	xhttp.send();

	//alert(url);
}

function ajaxPost(url, formId, resultId)
{
	result = "#" + resultId;	// where to show the results
	form = "#" + formId;		// the form to serialize the data fields

	$.ajaxSetup({
		// use the token set in the layout header: csrf-token
		headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
	});

	$.post( url, $(form).serialize() )
		.done(function(data) {
			$(result).text(data);	// show the text returned by the controller update method
		})
		.fail(function(xhr, status, error) {
			if (xhr && xhr.responseText) // responseText not defined if a different is loading
				$(result).text(url + ": error: " + xhr.responseText);
		})
		;
}

prevFocus = null;
function setFloat(obj, id)
{
	prevFocus = obj;
	$("#accent-chars-esp").show();
	$("#accent-chars-esp").appendTo("#" + id);
}

function setFocus(obj)
{
	prevFocus = obj;
}

function insertChar(char, id, isTinyMce)
{
	var txtarea = null;

	if (isTinyMce)
	{
		if (tinymce.activeEditor)
			tinymce.activeEditor.execCommand('mceInsertContent', false, char);
		else
			console.log('tinymce.activeEditor not set');

		return;
	}

	if (id != 0 && id != '0')
	{
		// if we're using the id parameter 
		txtarea = document.getElementById(id);
	}
	else if (!prevFocus || prevFocus == 'undefined')
	{
		console.log('id not set and prevFocus not set');
		return;
	}
	else
	{
		//
		// if we're using previous focus instead of the id 
		//
		var focusId = prevFocus.attr('id');
		
		if (!focusId)
		{
			console.log('"id" must be set for each control that calls this component');
			return;
		}
		else
		{
			txtarea = document.getElementById(focusId);
			if (!txtarea)
			{
				console.log('textarea not set: ' + txtarea);
				return;
			}
		}
	}

    var scrollPos = txtarea.scrollTop;
    var caretPos = txtarea.selectionStart;

    var front = (txtarea.value).substring(0, caretPos);
    var back = (txtarea.value).substring(txtarea.selectionEnd, txtarea.value.length);
    txtarea.value = front + char + back;
    caretPos = caretPos + char.length;
    txtarea.selectionStart = caretPos;
    txtarea.selectionEnd = caretPos;
    txtarea.focus();
    txtarea.scrollTop = scrollPos;
}

function setTab(event, tab)
{
	event.preventDefault();

	if (tab == 1)
	{
		$('#tab-text').show();
		$('#tab-title').hide();

		$('#nav-link-text').addClass('active');
		$('#nav-link-title').removeClass('active');
	}
	else
	{
		$('#tab-text').hide();
		$('#tab-title').show();

		$('#nav-link-text').removeClass('active');
		$('#nav-link-title').addClass('active');
	}

}

function setActiveTab(event, tabIdShow, tabBodyClass, tabLinkClass = null)
{
	event.preventDefault();

	$(tabBodyClass).hide();	
	$(tabIdShow).show();

	if (tabLinkClass != null)
	{
		$(tabLinkClass).removeClass('active');
		$(tabIdShow + '-nav-link').addClass('active');
	}
}

function toggleActiveTab(event, tabIdShow, tabIdMain, tabBodyClass)
{
	event.preventDefault();

	var idShow = tabIdShow;
	
	// if the target is already visible, then toggle to main tab	
	if ($(tabIdShow).is(':visible')) 
		idShow = tabIdMain;

	$(tabBodyClass).hide();	// hide all tabs
	$(idShow).show();		// show the indicated tab
}


function saveAndStay()
{
	alert('Not implemented yet');

	//$.post('/lesson/update/{{$record->id}}', $('#form-edit').serialize());

	$("#form-edit").submit(function(e) {

		e.preventDefault(); // avoid to execute the actual submit of the form.

		var form = $(this);
		var url = form.attr('action');

		$.ajax({
			   type: "POST",
			   url: url,
			   data: form.serialize(), // serializes the form's elements.
			   success: function(data)
			   {
				   alert(data); // show response from the php script.
			   }
			 });


	});

}

function refreshView()
{
	if ($("#preview").is(":visible"))
	{
		$("#preview").hide();
		$("#rich").show();

		tinymce.init({selector:'#text'});
	}
	else
	{
		tinymce.remove();

		$("#preview").html(
			$("#text").text()
		);

		$("#preview").show();
		$("#rich").hide();
	}
}

function setLessonMainPhoto(id, photoPath, photoId, photoDivId, mainPhotoId, titleId = 0)
{
    var path = photoPath + id;
    photoId = "#" + photoId;
    photoDivId = "#" + photoDivId;
    mainPhotoId = "#" + mainPhotoId;

    $(photoId).attr("src", path);
    $(photoId).show();

    $(photoDivId).show();
    $(photoDivId).attr("display", "block");

    $(mainPhotoId).val(id);

    if (titleId != 0)
    {
        titleId = "#" + titleId;
        $(titleId).val(makeTitle(id));
    }

    //alert($("#main_photo").val());
}

/* NOT USED
function showMainPhoto2()
{
    var selectedPhoto = $("#main_photo option:selected" ).val();
    if (selectedPhoto == 0)
    {
        // no photo selected
        $("#photo").hide();
    }
    else
    {
        var path = "{{$photoPath}}" + selectedPhoto;
        $("#photo").attr("src", path);
        $("#photo").show();
        $("#photo-div").show();
        $("#photo-div").attr("display", "block");
    }
}
NOT USED */

function makeTitle(filename)
{
    var s = filename;

    s = s.toLowerCase().replace(/.jpg/g, '');
    s = s.toLowerCase().replace(/.png/g, '');
    s = s.toLowerCase().replace(/.gif/g, '');

    s = s.replace(/-/g, ' ');
    s = s.replace(/_/g, ' ');

    s = s.trim();

    s = s.toLowerCase()
        .split(' ')
        .map((s) => s.charAt(0).toUpperCase() + s.substring(1))
        .join(' ');

    return s;
}

function numInc(id, amount)
{
	var input = $("#" + id);
	var value = Number(input.val()) + amount;
	input.val(Number(value) < 0 ? 0 : Number(value));
}

function conjugationsGen(fromId, toId)
{
	var title = $(fromId).val();
	ajaxexec('/definitions/conjugationsgenajax/' + title.trim(), toId, true);
}

function wordFormsGen(event, fromId, toId, pluralOnly = false)
{
	event.preventDefault();
	var word = $(fromId).val();
	var wordForms = $(toId).val(); // get current forms so we don't wipe them out
	var wordForms = getWordForms(word, wordForms, pluralOnly);
	$(toId).val(wordForms);
}

function getWordForms(word, wordForms, pluralOnly)
{
	var gen = '';
	var root = word.substring(0, word.length - 1);
	word = word.trim();
	wordForms = wordForms.trim();
	
	// if it ends in a consonant, add 'as', else add 's'
	// paciente = pacientes
	// alto = altos, alta, altas
	// capaz = capaces
	// reloj = relojes
	if (word.endsWith('o') || word.endsWith('a'))
	{
		if (pluralOnly)
		{
			gen += word + 's';
		}
		else
		{
			gen += root + 'o';
			gen += ', ' + root + 'os';
			gen += ', ' + root + 'a';
			gen += ', ' + root + 'as';
		}
	}
	else if (word.endsWith('e'))
	{
		gen += word + 's';
	}
	else if (word.endsWith('z'))
	{
		gen += root + 'ces';
	}
	// for all the rest, just add 'es'
	else //if (word.endsWith('r') || word.endsWith('j'))
	{
		gen += word + 'es';
	}
	
	if (wordForms.length > 0)
	{
		if (!wordForms.endsWith(',') && !wordForms.endsWith(';'))
			wordForms += ', ';
		else
			wordForms += ' ';
	}
	 
	return wordForms + gen;
}

function wordExists(title)
{
	ajaxexec('/definitions/wordexists/' + title.val().trim(), '#wordexists');
}

function scrollTo(className, heightAdjustment = 0)
{
	var e = $(className).first();
	var position = e.offset();
	var top_of_element = position.top;
	var bottom_of_element = position.top + e.outerHeight();
	var bottom_of_screen = $(window).scrollTop() + $(window).innerHeight();
	var top_of_screen = $(window).scrollTop();
	bottom_of_screen -= heightAdjustment; // apply any adjustment for viewport height

	if ((bottom_of_screen > bottom_of_element) && (top_of_screen < top_of_element))
	{
		// the element is visible, don't scroll
	} 
	else 
	{
		// the element is not visible, scroll to it
		window.scroll(position.left, position.top);
	}
}

function translateOnWebsite(event, destination, text)
{
	event.preventDefault();
	
	if (destination == 'spanishdict')
		window.open("https://www.spanishdict.com/translate/" + text + "");
	else if (destination == 'wordreference')
		window.open("https://www.wordreference.com/definicion/" + text + "");		
	else // everything else goes to google
		window.open("https://translate.google.com/#view=home&op=translate&sl=es&tl=en&text=" + text + "");
}

function isAlphanum(keyCode)
{	
	if (keyCode >= 65 && keyCode <= 90) // a-z, A-Z
		return true;

	if (keyCode == 32)
		return true;

	if (keyCode >= 48 && keyCode <= 57) // 0-9
		return true;
	
	return false;
}

function isDelete(keyCode)
{	
	if (keyCode == 8) // backspace
		return true;

	if (keyCode == 46) // delete
		return true;
	
	return false;
}

_delaySearchId = 0;
_lastSearchWord = '';
function searchDefinitions(event, textId, resultsId)
{
	// only search when alphanum char is pressed or removed with backspace/delete
	// note that: ctrl-v and ctrl-x still work because the 'v' and 'x' are caught
	// before it was searching on arrows, page up/down, etc
	if (isMobile)
	{
		// keycodes don't work for mobile
	}
	else
	{
		var doit = isAlphanum(event.keyCode) || isDelete(event.keyCode); 
		//d('onkeyup: ' + event.keyCode + ', alphanum or delete: ' + doit);
		if (!doit)
			return;
	}
	
	var debugOn = false;
	
	if (_delaySearchId != 0)
	{
		clearTimeout(_delaySearchId);
		_delaySearchId = 0;
	}

	var searchText = $(textId).val().trim();
	debug('search: ' + searchText, debugOn);

	// try to limit the numbre of server calls
	if (searchText.length > 1 && searchText == _lastSearchWord)
	{
		debug('search: not calling duplicate search: ' + _lastSearchWord, debugOn);
		return;
	}
	
	$(resultsId).html('');	

	//if (searchText.length != 1) // don't use so we can see all the words that start with a letter
	{		
		_delaySearchId = setTimeout(function(){
			debug('search server call on timer: ' + searchText, debugOn);
			_lastSearchWord = searchText;
			ajaxexec('/definitions/search-ajax/' + searchText + '', resultsId, false, searchDefinitionsCallack);}
			, 500
		);
	}

}

function searchDefinitionsCallack()
{
	// update the results count
	var count = $('#searchDefinitionsResultsTable tr').length;
	$('#searchDefinitionsResultsCount').text(count);
}

function heartDefinition(event, recordId, resultsId)
{
	event.preventDefault();	
	
	var target = '#' + event.target.id;
	if ($(target).hasClass('glyphicon-heart-empty'))
	{
		// heart it
		ajaxexec('/definitions/heart/' + recordId + '', resultsId);
		$(target).removeClass('glyphicon-heart-empty');
		$(target).addClass('glyphicon-heart');
	}
	else
	{
		// unheart it
		ajaxexec('/definitions/unheart/' + recordId + '', resultsId);
		$(target).removeClass('glyphicon-heart');
		$(target).addClass('glyphicon-heart-empty');
	}
}

function unheartDefinition(event, recordId, resultsId)
{
	event.preventDefault();		
	var target = '#' + event.target.id;
	ajaxexec('/definitions/unheart/' + recordId + '', resultsId);
}

function toggleWip(event, recordId, resultsId)
{
	event.preventDefault();	
	ajaxexec('/definitions/toggle-wip/' + recordId + '', resultsId);
	var target = '#' + event.target.id;
	if ($(target).hasClass('glyphicon-ok-circle'))
	{
		$(target).removeClass('glyphicon-ok-circle');
		$(target).addClass('glyphicon-remove-sign');
	}
	else
	{
		$(target).removeClass('glyphicon-remove-sign');
		$(target).addClass('glyphicon-ok-circle');
	}
}

