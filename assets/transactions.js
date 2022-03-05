/*
	=======================================================
	Monzo API & PHP Integration
		-GH:		https://github.com/tomludlow2/monzo_api
		-Monzo:		https://docs.monzo.com/

	Created By:  	Tom Ludlow   tom.m.lud@gmail.com
	Date:			Feb 2022

	Tools / Frameworks / Acknowledgements 
		-Bootstrap (inc Icons):	MIT License, (C) 2018 Twitter 
			(https://getbootstrap.com/docs/5.1/about/license/)
		-jQuery:		MIT License, (C) 2019 JS Foundation 
			(https://jquery.org/license/)
		-Monzo Developer API
	========================================================
		file_name:  transactions.js
		function:	simply adds tooltip functionality
		todo: 		add filter for table
*/

$(function() {
	//Ready
	var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
	var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
	  return new bootstrap.Tooltip(tooltipTriggerEl)
	});
	$("#transaction_table").dblclick(function(event) {
		if($(this).hasClass("table-sm")) {
			$(this).removeClass("table-sm");
		}else {
			$(this).addClass("table-sm");
		}
	});
});