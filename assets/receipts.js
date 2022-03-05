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
		file_name:  receipts.js
		function:	provide the local JS to manage all the 
					receipts functions. jQuery
*/

var transactions = [];
var receipts = [];
var delete_conf = false;
var create_receipt = {};

$(function() {
	load_data();
	$('#receipt_select').on("change", function(event) {
		$('#response_output').html("");
		$('#create_receipt_holder').hide();
		$('#manage_controls').show();
		let receipt = receipts[$(this).val()];
		$('#items_readout').text( write_receipt(receipt)).removeClass("bg-success").removeClass("bg-warning");
	});

	$('#receipt_delete').on("click", function(event) {
		if( delete_conf == false ) {
			var conf = confirm("Due to a problem with the Monzo API, deleting a receipt does not work correctly, proceeding with this action will instead overwrite the existing receipt with a placeholder, do you wish to continue?");
			$(this).removeClass("btn-outline-danger").addClass("btn-danger");
			if( conf ) {
				delete_conf = true;
			}
		}else {
			let receipt = $("#receipt_select").val();			
			$.post("null_receipt.php", {receipt_id:receipt}, function(data) {
				if( data.success == "SUCCESS" ) {
					$('#items_readout').text("Success - Receipt Deleted\nSelect a new Option");
				}else {
					alert("Error, please see the JSON output box below");
				}
				$('#response_output').html("<pre class='text-start'>" + JSON.stringify(data, null, 2) + "</pre>");
			}, "json");
			load_data();
			$('.controls').hide();
			$('#create_receipt_holder').show();
		}
	});

	$('#receipt_validate').on("click", function(event) {
		let receipt = $("#receipt_select").val();
		$.post("get_receipt.php", {receipt_id:receipt}, function(data) {
			console.log(data);
			//Get the local data from the currently loaded information
			let local_data = receipts[data.receipt_id];

			//Get the remote data from the Monzo API verbatim. 
			let remote_data = data.response.receipt;

			//Setup a validation holder
			let validate = {};

			//First check amounts
			validate.check_amounts = 0;
			//**2 is square, to account for negative values
			if( (local_data.amount **2 ) == (remote_data.total ** 2)) {
				validate.check_amounts = 1;
			}

			//Then check specific items
			let local_items = JSON.parse(receipts[data.receipt_id].content);
			let remote_items = data.response.receipt.items;
			validate.check_item_count = 0;
			if( local_items.length == remote_items.length) {
				validate.check_item_count = 1;
			}
			let validation_count = 0;
			for (var i = local_items.length - 1; i >= 0; i--) {
				validation_count += validation_check(local_items[i], remote_items);				
			}
			validate.check_matched_items = 0;
			if( local_items.length == validation_count ) {
				validate.check_matched_items = 1;
			}

			let output_comp = {
				validate: validate,
				local_amount: local_data.amount,
				remote_amount: remote_data.total,
				local_items: local_items,
				remote_items: remote_data.items
			};

			if( (validate.check_matched_items + validate.check_item_count + validate.check_amounts) == 3) {
				$('#items_readout').addClass("bg-success");
			}else {
				$('#items_readout').addClass("bg-warning");
				alert("There is a validation problem between the Monzo and Local Servers");
			}
			$('#response_output').html("<pre class='text-start'>" + JSON.stringify(output_comp, null, 2) + "</pre>");
		}, "json");
	});

	$('#transaction_select').on("change", function(event) {
		$('#response_output').html("");
		$('#manage_receipt_holder').hide();
		$('#create_controls').hide();
		let transaction_id = $('#transaction_select').val();	

		//Populate the new receipt initially with the transaction information
		create_receipt = transactions[transaction_id];		
		create_receipt.content = JSON.stringify([]);
		create_receipt.current_items = [];
		
		//Check if exists already
		$.post("check_receipt.php", {transaction_id:transaction_id}, function(data) {
			if( data.receipt_found == true ) {
				//console.log(data);
				$('#response_output').html("<pre class='text-start'>" + JSON.stringify(data, null, 2) + "</pre>");
				$('#overwrite_holder').show();
				$('#items_readout').text( write_receipt(data)).removeClass("bg-success").removeClass("bg-warning");

			}else {
				console.log("No Existing Receipt Found");
				$('#create_controls').show();
				$('#items_readout').text( write_receipt(create_receipt) );
			}
		}, "json");
	});

	$('#overwrite_receipt').on("click", function(event) {
		$('#overwrite_holder').show();
		$('#create_controls').show();
		$('#items_readout').text("");
		$('#overwrite_holder').hide();
	});
	
	$('#add_item').on("click", function(event) {
		$('#items_readout').removeClass("bg-warning").removeClass("bg-success");
		$('#subtotal_incorrect_holder').hide();
		let item_amount = Math.round($('#receipt_amount').val()*100);
		let quantity = Math.round($('#receipt_quantity').val()*100)/100;
		let total_amount = item_amount * quantity;
		let item = {
			description: $('#receipt_description').val(),
			tax: 0,
			amount: total_amount,
			quantity: quantity,
			units: $('#receipt_units').val(),
			currency: "GBP"
		};
		
		create_receipt.current_items.push(item);
		create_receipt.content = JSON.stringify(create_receipt.current_items.reverse());
		$('#items_readout').text( write_receipt(create_receipt) );

		$('#receipt_description').val("");
		$('#receipt_amount').val(0);
		$('#receipt_units').val("");
		$('#receipt_quantity').val(1);
	});

	
	$('#subtotal_receipt').on("click", function(event) {
		$('#subtotal_incorrect_holder').hide();
		$('#subtotal_difference').text("The difference is £0.00");
		var running_total = 0;
		for (var i = create_receipt.current_items.length - 1; i >= 0; i--) {
			running_total += create_receipt.current_items[i].amount;
		}
		let diff = create_receipt.amount*-1 - running_total;

		if( diff == 0 ) {
			$('#items_readout').addClass("bg-success").removeClass("bg-warning");
		}else {
			$('#items_readout').addClass("bg-warning").removeClass("bg-success");
			$('#subtotal_difference').text("The difference is " + (diff/100).toLocaleString("en-GB", {style: "currency", currency: "GBP", minimumFractionDigits: 2}) );
			$('#subtotal_incorrect_holder').show();
			$('#receipt_amount').val((diff/100));
		}		
	});

	
  	$('#create_receipt').click(function(event) {
		let data = {new_receipt:create_receipt};
		$.post( "add_receipt.php", data, function(data) {
			console.log(data);
			let op = JSON.stringify(data, null, 2);
			$('#response_output').html("<pre class='text-start'>" + op + "</pre>");
		}, "json");
	});
	
});

function load_data() {
	$.post("populate_receipt_page.php", {limit:25}, function(data) {
		//console.log(data);
		transactions = [];
		receipts = [];
		create_receipt = {};
		if( data.success ) {
			for (var i = 0; i <= data.transactions_data.transactions.length-1; i++) {
				transactions[data.transactions_data.transactions[i]['trans_id']] = data.transactions_data.transactions[i];
			}
			for (var i = 0; i <= data.receipt_data.transactions.length -1; i++) {
				receipts[data.receipt_data.transactions[i]['receipt_id']] = data.receipt_data.transactions[i];
			}
			//Populate the selects
			let trans_select = produce_select(data.transactions_data.transactions, "trans");
			let receipts_select = produce_select(data.receipt_data.transactions, "receipts");
			$('#transaction_select').html( trans_select);
			$('#receipt_select').html( receipts_select);
		}
	}, "json");
}

function produce_select(rows, mode) {
	let r = "";
	if( mode == "trans" ) {
		r += "<option selected>Select a transaction</option>"
	}else if( mode == "receipts") {
		r += "<option selected>Select a receipt</option>"
	}
	for (var i = 0; i <= rows.length-1; i++) {
		if( mode == "trans") {
			r += "<option value='" + rows[i]['trans_id'] + "'>";
		}else if( mode == "receipts") {
			r += "<option value='" + rows[i]['receipt_id'] + "'>";
		}
		r += rows[i]['created'] + " - " + rows[i]['human_amount'] + " - " + rows[i]['description'];
		r += "</option>";
	}
	return r;
}

function write_receipt(receipt) {
	//A bit clunky - TODO - look for receipt formatter
	let r = "MONZO API RECEIPT\n";	
	r += "---Receipt for " + receipt.trans_id + "---\n";
	r += "---Local Ref " + receipt.receipt_id + "---\n";
	r += "Date: \t" + receipt.created + "\n";
	r += "Total:\t" + receipt.human_amount + "\n";
	r += "-------------------------------------------\n";
	r += receipt.description + "\n";
	r += "-------------------ITEMS-------------------\n";

	let receipt_items = JSON.parse(receipt.content);
	for (var i = receipt_items.length - 1; i >= 0; i--) {
		let item = receipt_items[i];
		r += item.quantity + item.units + "\t" + item.description.padEnd(30," ") + "\t\t" + (item.amount/100).toLocaleString("en-GB", {style: "currency", currency: "GBP", minimumFractionDigits: 2}) + "\n";
		if( item.description == "Transaction Value") {
			r += "\n-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-\n--";
			r += "\nInfo: This receipt is a placeholder for a previous deleted recepit.\n--\n";
			r += "-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-#-\n";
		}
	}
	r += "\n-------------------------------------------\n\n";
	r += "Total:\t" + (-1*receipt.amount/100).toLocaleString("en-GB", {style: "currency", currency: "GBP", minimumFractionDigits: 2});
	r += "\n\n-------------------------------------------\n";
	r += "Receipt Generated by MonzoAPI";
	r += "\n-------------------------------------------\n";
	return r;
}

function validation_check(item, other_items) {
	let validation_string = item.description + item.amount + item.quantity;	
	for (var i = other_items.length - 1; i >= 0; i--) {
		let test_string = other_items[i].description + other_items[i].amount + other_items[i].quantity;
		if( test_string == validation_string) {
			return 1;
		}
	}
	return 0;
}