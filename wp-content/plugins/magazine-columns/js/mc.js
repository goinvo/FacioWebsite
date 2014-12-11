var columns_toolbar = document.getElementById("ed_toolbar");

if (columns_toolbar) {
	var startButton = document.createElement('input');
	startButton.type = 'button';
	startButton.value = 'start columns';
	startButton.onclick = start_button;
	startButton.className = 'ed_button';
	startButton.title = 'start columns';
	startButton.id = 'ed_startColumn';
	columns_toolbar.appendChild(startButton);

	var columnButton = document.createElement('input');
	columnButton.type = 'button';
	columnButton.value = 'column';
	columnButton.onclick = columns_button;
	columnButton.className = 'ed_button';
	columnButton.title = 'column';
	columnButton.id = 'ed_column';
	columns_toolbar.appendChild(columnButton);

	var stopButton = document.createElement('input');
	stopButton.type = 'button';
	stopButton.value = 'stop columns';
	stopButton.onclick = stop_button;
	stopButton.className = 'ed_button';
	stopButton.title = 'stop columns';
	stopButton.id = 'ed_stopColumn';
	columns_toolbar.appendChild(stopButton);
}

function start_button(querystr) {
	var text = "<!--startcolumns-->";
	pos(text)
}

function columns_button(querystr) {
	var text = "<!--column-->";
	pos(text);
}

function stop_button(querystr) {
	var text = "<!--stopcolumns-->";
	pos(text);
}

function pos(text) {
	var areaId = 'content';
	var txtarea = document.getElementById(areaId);
	var scrollPos = txtarea.scrollTop;
	var strPos = 0;
	var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
		"ff" : (document.selection ? "ie" : false ) );
	if (br == "ie") { 
		txtarea.focus();
		var range = document.selection.createRange();
		range.moveStart ('character', -txtarea.value.length);
		strPos = range.text.length;
	}
	else if (br == "ff") strPos = txtarea.selectionStart;
	
	var front = (txtarea.value).substring(0,strPos);  
	var back = (txtarea.value).substring(strPos,txtarea.value.length); 
	txtarea.value=front+text+back;
	strPos = strPos + text.length;
	if (br == "ie") { 
		txtarea.focus();
		var range = document.selection.createRange();
		range.moveStart ('character', -txtarea.value.length);
		range.moveStart ('character', strPos);
		range.moveEnd ('character', 0);
		range.select();
	}
	else if (br == "ff") {
		txtarea.selectionStart = strPos;
		txtarea.selectionEnd = strPos;
		txtarea.focus();
	}
	txtarea.scrollTop = scrollPos;	
}