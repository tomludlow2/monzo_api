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