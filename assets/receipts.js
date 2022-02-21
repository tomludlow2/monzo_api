var transactions = [];
var receipts = [];
var next_count = "";
var delete_conf = false;
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

	/*
	var c_r = "";
	var c_items = [];
	var c_total = 0;
	var c_subtotal = 0;
	var c_receipt_id = "";
	$('#transaction_select').on("change", function(event) {
		c_subtotal = 0;
		C_items = [];
		/*Note that txs is written verbatim by php at origin
		//Get the transaction info out:
		let selected_transaction = txs[$('#transaction_select').val()];
		console.log(selected_transaction);


		//Check if receipt exists
		$.post("check_receipt.php", {transaction_id:selected_transaction.trans_id}, function(data) {
			if( data.receipt_found == true ) {
				console.log(data);
				//Currently - simply offer the choice to remove the old receipt rather than edit items
				let old_receipt = JSON.parse(data.receipt_content);
				let t_id = data.transaction_id;
				let r_id = data.receipt_id;
				c_receipt_id = r_id;
				let total = data.monzo_response.receipt.total.toLocaleString("en-GB", {style: "currency", currency: "GBP", minimumFractionDigits: 2});
				c_r = "MONZO API RECEIPT\n";
				c_r += "---Receipt for " + t_id + "---\n";
				c_r += "---Local Ref " + r_id + "---\n";
				c_r += "Date: \t" + selected_transaction.created + "\n";
				c_r += "Total:\t" + selected_transaction.human_amount + "\n";
				c_r += "-------------------------------------------\n";
				c_r += selected_transaction.description + "\n";
				c_r += "-------------------ITEMS-------------------\n";

				for (var i = old_receipt.length - 1; i >= 0; i--) {
					let item = old_receipt[i];
					c_r += item.quantity + item.units + "\t" + item.description.padEnd(30," ") + "\t\t" + (item.amount/100).toLocaleString("en-GB", {style: "currency", currency: "GBP", minimumFractionDigits: 2}) + "\n";
				}

				c_r += "\n-------------------------------------------\n\n";
				c_r += "Total:\t" + (data.monzo_response.receipt.total/100).toLocaleString("en-GB", {style: "currency", currency: "GBP", minimumFractionDigits: 2});
				c_r += "\n\n-------------------------------------------\n";
				c_r += "Receipt Generated by MonzoAPI";
				c_r += "\n-------------------------------------------\n";

				
				$('.form-control, .form-select, .btn').each(function(index) {
					$(this).hide();
				});
				$('#delete_receipt').css("display", "inline").prop("disabled", false);
				$('#delete_message').show();

			}else {
				c_r = "MONZO API RECEIPT\n";
				c_r += "---Receipt for " + selected_transaction.trans_id + "---\n";


				c_r += "Date: \t" + selected_transaction.created + "\n";
				c_r += "Total:\t" + selected_transaction.human_amount + "\n";
				c_r += "-------------------------------------------\n";
				c_r += selected_transaction.description + "\n";
				c_r += "-------------------ITEMS-------------------\n";

				c_total = selected_transaction.amount * -0.01;
				$('#add_item').prop("disabled", false);
				$('#validate_receipt').prop("disabled", false);
			}
			$('#items_readout').text(c_r);	
		}, "json");	
		
	});


	$('#add_item').on("click", function(event) {
		let item_amount = Math.round($('#receipt_amount').val()*100)/100;
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
		c_items.push(item);

		c_r += item.quantity + item.units + "\t" + item.description.padEnd(30," ") + "\t\t" + item.amount.toLocaleString("en-GB", {style: "currency", currency: "GBP", minimumFractionDigits: 2}) + "\n";
		c_subtotal += Math.round(item.amount*100)/100;
		$('#items_readout').text(c_r);

		$('#receipt_description').val("");
		let diff = c_total - c_subtotal;
		$('#receipt_amount').val(Math.round(diff*100)/100);
		$('#receipt_units').val("");
		$('#receipt_quantity').val(1);
	});

	$('#validate_receipt').on("click", function(event) {
		var proceed = 0;
		if( Math.round(c_subtotal*100)/100 == Math.round(c_total*100)/100 ) {
			//Valid
			proceed = 1;
		}else {
			//Invalid
			var conf = confirm("The subtotal of the items you have added is not equal to the total of the transaction. Are you happy with this?");
			if(conf) {
				proceed = 1;
			}else {
				$('#receipt_description').val("Missing Item");
				let diff = c_total - c_subtotal;
				$('#receipt_amount').val(Math.round(diff*100)/100);
				$('#receipt_units').val("");
				$('#receipt_quantity').val(1);
			};
		}

		if(proceed) {
			c_r += "\n\n-------------------------------------------\n\n";
			c_r += "Total:\t£" + c_total;
			c_r += "\n\n-------------------------------------------\n";
			c_r += "Receipt Generated by MonzoAPI";
			c_r += "\n-------------------------------------------\n";
			$('.form-control, .form-select, .btn').each(function(index) {
				$(this).attr("disabled", "disabled");
			});
			$('#add_receipt').prop("disabled", false);

			$('#items_readout').text(c_r);
		}
	});

  	$('#add_receipt').click(function(event) {
		

		let trans_id = $('#transaction_select').val();
		let trans = txs[trans_id];
		let items = c_items;

		let data = {
			transaction_id : trans_id,
			transaction_amount : trans.amount*-1,
			transaction_items : c_items
		};


		console.log(trans_id, trans.amount, items);


		$.post( "add_receipt.php", data, function(data) {
			console.log(data);
			let op = JSON.stringify(data, null, 2);
			$('#response_output').html("<pre class='text-start'>" + op + "</pre>");
		}, "json");
	});

	$('#delete_receipt').click(function(event) {
		//Send the delete_receipt request
		console.log("Deleting receipt", c_receipt_id);
		$.post("delete_receipt.php", {receipt_id:c_receipt_id}, function(data) {
			let op = JSON.stringify(data, null, 2);
			$('#response_output').html("<pre class='text-start'>" + op + "</pre>");
		},"json");

		if(data.success == "SUCCESS") {
			$('#delete_message').text("Receipt Deleted - please refresh the page to continue").css("bg-success");
		}else {
			$('#delete_message').text("There was an error deleting the receipt, see below, then please refresh the page to continue").css("bg-warning");
		}
	});

	*/
});

function load_data() {
	$.post("populate_receipt_page.php", {limit:25}, function(data) {
		console.log(data);
		transactions = [];
		receipts = [];
		if( data.success ) {
			for (var i = data.transactions_data.transactions.length - 1; i >= 0; i--) {
				transactions[data.transactions_data.transactions[i]['trans_id']] = data.transactions_data.transactions[i];
			}
			for (var i = data.receipt_data.transactions.length - 1; i >= 0; i--) {
				receipts[data.receipt_data.transactions[i]['receipt_id']] = data.receipt_data.transactions[i];
			}
			next_count = data.next_count;

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
	for (var i = rows.length - 1; i >= 0; i--) {
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