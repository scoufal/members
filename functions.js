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


/*
  Functions for finance tables - start
*/

function initCheckboxGroups(changePostprocessing = null) {

	// Handle click on "all" → toggle all "one"
	document.querySelectorAll("input[type=checkbox][data-role='all']").forEach(allBox => {
		allBox.addEventListener("click", function() {
			const checked = this.checked;

			this.closest(".checkbox-row").querySelectorAll("input[type=checkbox]")
				.forEach(box => {
					if ( box != this ) box.checked = checked;
				});
			if ( changePostprocessing ) updateRows(changePostprocessing);
		});
	});

	// Handle click on "one" → maybe update "all"
	document.querySelectorAll("input[type=checkbox][data-role='one']").forEach(oneBox => {
		oneBox.addEventListener("click", function() {
			const allBox = this.closest(".checkbox-row").querySelector("input[type=checkbox][data-role='all']");

			if (allBox) { // only if "all" exists
				if (this.checked) {
					// if all "one" are checked, "all" might be checked
					const allOnes = this.closest(".checkbox-row").querySelectorAll("input[type=checkbox][data-role='one']");
					const allChecked = Array.from(allOnes).every(cb => cb.checked);
					allBox.checked = allChecked;
				} else {
					// if one is unchecked, "all" must be unchecked
					allBox.checked = false;
				}
			}
			if ( changePostprocessing ) updateRows(changePostprocessing);
		});
	});
}

function setSelectedState(selector, state) {
	selector.className = "state " + state;
	selector.textContent = (state === "selected") ? "✔" : "📌";
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

// modify all rows with selector, lambda function parameters are row, element with selector class and current state
function updateRowsByState(rowModifier) {
	const selectors = document.querySelectorAll('tr td .state');

	selectors.forEach(selector => {
		row = selector.closest("tr");
		const state = Array.from(selector.classList).find(c => c !== "state");
		rowModifier(row, selector, state);
	});
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

	const rows = document.querySelectorAll('tr' + selector);

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

const financialState = new Map(); // change tracking for rollback

function addStateValue(row, colName, newVal) {
  if (!financialState.has(row)) {
    financialState.set(row, {});
  }
  const rowObj = financialState.get(row);

  rowObj[colName] = (rowObj[colName] || 0) + Number(newVal);
}

function swapStateValue(row, colName, newVal) {
  if (!financialState.has(row)) {
    financialState.set(row, {});
  }
  const rowObj = financialState.get(row);

  let prevValue = (rowObj[colName] || 0);

  // set new value
  rowObj[colName] = Number(newVal);

  // return the previous one
  return prevValue;
}

function fillFinanceRow( row, perform, values ) {
	// update row values
	let addNote = [];
	let addAmount = 0;
	let noteField = null;
	let amountField = null;
	let clearRegex = new Set(); // list of keys removed from note

	const colTextMap = {
		entryFee: 'startovné',
		transport: 'doprava',
		accommodation: 'ubytování'
		};

	row.querySelectorAll('[data-col]').forEach(cell => {
		const colName = cell.dataset.col;
		if (values.hasOwnProperty(colName)) {
			if (colName === 'amount') amountField = cell; // remember amount cell
			if (colName === 'note') noteField = cell; // remember note cell
			const newVal = values[colName];
			let addVal = ''; // value in add format
			if ( perform === 'payrule' ) {
				const as = row.dataset.as ?? '';
				if ( as == 0 ) { // only prihlaseni
					const finType = row.dataset.fintype ?? '';	
					const startTier = row.dataset.startTier ?? '';
					for (const [financeType, terms] of Object.entries(payrules)) { //loop throught finance types
						if ( financeType !== finType && financeType !== '' ) continue; // only matching type or default
						for (const [termin, data] of Object.entries(terms)) { // loop through terms
							if ( startTier && termin !== startTier && termin !== '' ) continue; // only matching tier or default
							// Loop through all triplets [zebricek, platba, typ_platby]
							for (const entry of data) {
								const [zebricek, platba, typ_platby, uctovano] = entry;
								console.log(`fintype=${financeType}, termin=${termin}  → zebříček=${zebricek}, platba=${platba}, typ_platby=${typ_platby}, uctovano=${uctovano}`);
							}
						}
					}
				}
			}
			if ( newVal == null ) addVal = '';
			else if (!isNaN(newVal)) addVal = (Number(newVal) >= 0 ? "+" : "") + Number(newVal);
			else addVal = '/' + newVal;

			// Only update if no data-fill OR data-fill == "1"
			if ((!cell.dataset.fill || cell.dataset.fill === "1") ) {
				switch (perform) {
					case 'overwrite': // přepiš
						if ( newVal !== '' ) { // null or set							
							if (cell.tagName === "INPUT" || cell.tagName === "TEXTAREA") {
								cell.value = newVal;
							} else {
								if ( cell.dataset.fill ) {
									cell.textContent = '✔' + addVal;
								} else {
									cell.textContent = newVal;
								}
							}
							if ( colTextMap[colName]) {						
								if ( newVal ) {
									// effective value
									addNote.push(addVal + ' ' + colTextMap[colName]);
									addAmount += Number(newVal); // add new
								}
								addAmount -= swapStateValue ( row, colName, newVal ); // remove old and save new
								clearRegex.add(colTextMap[colName]); // mark remove from note
							}
						}
						break;

					case 'insert': // vlož
					case 'payrule': // vlož podle pravidel
					    if ( newVal ) {
							let wasEmpty = false;
							if (cell.tagName === "INPUT" || cell.tagName === "TEXTAREA") {
								if ( !cell.value.trim() ) {
									wasEmpty = true;
									cell.value = newVal;
								}								
							} else {
								if ( cell.dataset.fill ) {
									if ( cell.textContent === '✔' ) {
										wasEmpty = true;
										cell.textContent = '✔' + addVal;
									}
								} else {
									if ( !cell.textContent.trim() ) {
										wasEmpty = true;
										cell.textContent = newVal;
									}
								}
							}
							if (wasEmpty && colTextMap[colName]) {
								addNote.push(addVal + ' ' + colTextMap[colName]);
								addStateValue(row, colName, newVal); // save new
								addAmount += Number(newVal); // add new
							}
						}
						break;

					case 'add': // přidej
					    if ( newVal ) {
							if (cell.tagName === "INPUT" || cell.tagName === "TEXTAREA") {
								if (colName === 'amount') {
									if ( !isNaN ( cell.value ) ) cell.value = Number(cell.value) + Number ( newVal );
								} else {
									if (!cell.value.trim()) cell.value = newVal; else cell.value += addVal;
								}
							} else {
								if (!cell.textContent.trim()) cell.textContent = newVal; else cell.textContent += addVal;
							}
							if (colTextMap[colName]) {
								addNote.push(addVal  + ' ' + colTextMap[colName]);
								addStateValue ( row, colName, newVal); // save added
								addAmount += Number(newVal); // add added
							}
						}
						break;

					default:
					// do nothing
				}
			}
		}
	});

	if ( noteField && clearRegex.size > 0 ) {
		let noteText = noteField.value;
		for (const value of clearRegex ) {
			const regex = new RegExp('[+-]?\\d+\\s*' + value, "g");
			noteText = noteText.replaceAll( regex, '' );
		}
		noteField.value = noteText;
	}
	if (noteField && addNote.length > 0) {
		noteField.value = noteField.value + addNote.join('');
	}
	if (amountField && addAmount) {
		amountField.value = Number(amountField.value) + addAmount;
	}

}

function fillTableFromInput(perform, event) {
	// prevent form submission
	event.preventDefault();

	// collect input values
	const values = {};
	document.querySelectorAll(".form-row [id^='in-']")
		.forEach(input => {
			const key = input.id.substring(3); // remove "in-"
			if (key.endsWith("-null")) {
				if (input.classList.contains("pinned")) {
					// null marker → set corresponding value to null
					const baseKey = key.slice(0, -5); // cut off "-null"
					values[baseKey] = null;
				}
			} else {
				// only set if not already set (to avoid overwriting null)
				if (!(key in values)) {
					values[key] = input.value;
				}
			}
		}
	);

	document.querySelectorAll("tr td .state")
		.forEach(input => {
			if (input.classList.contains("selected") || input.classList.contains("pinned") || perform === 'payrule') {
				row = input.closest("tr");
				fillFinanceRow(row,perform,values);
			}
		});
};

/*
  Functions for finance tables - end
*/
