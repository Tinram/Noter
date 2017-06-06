
window.addEventListener("load", function() {

	// block empty search field from going off to server

	if (document.getElementById("fs")) {

		document.getElementById("term").focus();

		document.getElementById("fs").addEventListener("submit", function(e) {

			if (document.getElementById("term").value === "") {
				document.getElementById("fserror").innerHTML = "Search term is empty!";
				document.getElementById("term").focus();
				e.preventDefault();
			}
		},

		false);
	}


	if (document.getElementById("faddcont")) {
		document.getElementById("title").focus();
	}


	if (document.getElementById("complete")) {
		document.getElementById("complete").className = "fade";
	}

}, false);
