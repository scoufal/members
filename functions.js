/*	members - online prihlaskovy system	*/

def_width = 400;
def_height = 400;
def_race_url = '';

function set_default_size(width, height)
{
	def_width = width;
	def_height = height;
}

function set_default_race_url(url)
{
	def_race_url = url;
}

function open_win_ex(url,win_name,width, height)
{
	nwin = window.open(url, win_name, 'toolbars=0, scrollbars=1, location=0, status=0, menubar=0, resizable=1, left=0, top=0, width='+width+', height='+height);
	nwin.focus();
}

function open_win(url,win_name)
{
	nwin = window.open(url, win_name, 'toolbars=0, scrollbars=1, location=0, status=0, menubar=0, resizable=1, left=0, top=0, width='+def_width+', height='+def_height);
	nwin.focus();
}

function open_win2(url,win_name)
{
	nwin = window.open(url, win_name, 'toolbars=0, scrollbars=1, location=0, status=1, menubar=1, resizable=1, left=0, top=0, width='+def_width+', height='+def_height);
	nwin.focus();
}

function open_race_info(id)
{
	nwin = window.open(def_race_url+id, '', 'toolbars=0, scrollbars=1, location=0, status=0, menubar=0, resizable=1, left=0, top=0, width=500, height=480');
	nwin.focus();
}

function open_url(url)
{
	window.open(url, "_self");
}

function close_popup()
{
	if (window.opener)
	{
		window.opener.focus();
	}
	window.close();
}

function close_win()
{
	window.close();
}

function checkAll( field, flag )
{
	var elements = document.getElementById(field).getElementsByTagName('input');
	if(!elements)
		return;

	for (i = 0; i < elements.length; i++)
	{
		if ( elements[i].type == 'checkbox' )
			elements[i].checked = flag ;
	}
}

function isValidDate(subject)
{
	// Idea for new code taken from :
	// Original JavaScript code by Chirp Internet: www.chirp.com.au
	// Please acknowledge use of this code by including this header.
 
	var minYear = 1902;

	// regular expression to match required date format
	re = /^(\d{1,2})[\- \/.](\d{1,2})[\- \/.](\d{4})$/;

	if(regs = subject.match(re))
	{
		if(regs[1] < 1 || regs[1] > 31)
			return false;
		else if(regs[2] < 1 || regs[2] > 12)
			return false;
		else if(regs[3] < minYear)
			return false;
		else
			return true;
	} 
	return false;
}

function isValidLogin(subject)
{
  if (subject.match(/^[[a-zA-Z/._-][a-zA-Z0-9/._-]*$/)) // prvni znak neni cislo
  { 
    return true;
  }
  else
  {
    return false;
  }
}

function isPositiveNumber(subject)
{
	num = parseInt(subject.value);
	if (num > 0) return true;
	alert("Číslo musí být kladné");
	return false;
}

function haveMoney(subject, subject_sum)
{
	num = parseInt(subject.value);
	sum = parseInt(subject_sum.value);
	if (num <= sum) return true;
	alert("Nemáte dostatek peněz pro převod.");
	return false;
}

function changeParameterValueInURL(currentUrl, parameter, value)
{
	var url = new URL(currentUrl);
	url.searchParams.set(parameter, value);
	return url.href;
}

function toggle_display_by_class(cls) {
    var lst = document.getElementsByClassName(cls);
    for(var i = 0; i < lst.length; ++i) {
        (lst[i].style.display == '')?(lst[i].style.display='none'):(lst[i].style.display='');
    }
}

function toggleDisplayByToggleClass(cls) {
	let elems = document.getElementsByClassName(cls)
	Array.prototype.forEach.call(elems, function(el) {
		$( el ).toggleClass("hidden");
	});
}

function toggleDisplayByData(key,value) {

    var lst = document.querySelectorAll('[' + key + '="' + value + '"]');

	for(var i = 0; i < lst.length; ++i) {
        (lst[i].style.display == '')?(lst[i].style.display='none'):(lst[i].style.display='');
    }
}

function getCheckedIds (key) {

	const allCheckboxes = Array.from(document.querySelectorAll('input[type="checkbox"][data-key="' + key + '"][data-role="one"]'));
	const checkedValues = allCheckboxes.filter(cb => cb.checked).map(cb => cb.value);

	if ( checkedValues.length == 0 ) {
		return null;
	} else {
		return new Set( checkedValues );
	}

}

function updateRows(rowModifier) {

  // Collect all checkbox groups
  const groups = {};
  document.querySelectorAll("div.checkbox-row[data-key]").forEach(div => {
    const key = div.dataset.key; // e.g. "fintype", "kat", ...
    groups[key] = getCheckedIds(key); // Set or null
  });

 // Build selector: rows with at least one data-* attribute defined
  const selector = Object.keys(groups)
    .map(key => `[data-${key}]`)
    .join(",");
  console.log("Selector:", selector);

  const rows = document.querySelectorAll('tr' + selector);
  console.log("Rows:", rows.length);
  
  rows.forEach(row => {
	match = true;

	for (const [key, checkedTypes] of Object.entries(groups)) {
		if ( row.dataset[key] ) { // only if the row has this data-key attribute
			if ( !checkedTypes )  { match = false; break; } // no value selected for this key
			if ( !checkedTypes.has(row.dataset[key]) ) { match = false; break; } // not in the filter
		}
	}

    rowModifier(row, match);
  });
}