$(function() {
	$(".deposit_button").on("click", function(event) {
		let pot_id = $(this).attr("pot_id");
		if( $("input[pot_id='" + pot_id + "']").val() != "" ) {
			//There is an amount - go
			let amount = Math.round($("input[pot_id='" + pot_id + "']").val()*100);
			let human_amount = (amount/100).toLocaleString("en-GB", {style: "currency", currency: "GBP", minimumFractionDigits: 2});
			$("input[pot_id='" + pot_id + "']").val(human_amount);
			console.log(pot_id, amount, human_amount);
			$(this).html("Depositing <img src='assets/loading.gif' height='20px' />");
			process_move("deposit", pot_id, amount);
		}else{
			//There is no amount - setup		
			$("input[pot_id='" + pot_id + "']").parent().show();
			$(".withdraw_button[pot_id='" + pot_id + "']").attr("disabled", true);
			$(this).removeClass("btn-outline-primary").addClass("btn-success");
		}
	});

	$(".withdraw_button").on("click", function(event) {
		let pot_id = $(this).attr("pot_id");
		if( $("input[pot_id='" + pot_id + "']").val() != "" ) {
			//There is an amount - go
			let amount = Math.round($("input[pot_id='" + pot_id + "']").val()*100);
			let human_amount = (amount/100).toLocaleString("en-GB", {style: "currency", currency: "GBP", minimumFractionDigits: 2});
			$("input[pot_id='" + pot_id + "']").val(human_amount);
			console.log(pot_id, amount, human_amount);
			$(this).html("Withdrawing <img src='assets/loading.gif' height='20px' />");
			process_move("withdraw", pot_id, amount);
		}else{
			//There is no amount - setup		
			$("input[pot_id='" + pot_id + "']").parent().show();
			$(".deposit_button[pot_id='" + pot_id + "']").attr("disabled", true);
			$(this).removeClass("btn-outline-secondary").addClass("btn-success");
		}
	});
	
});


function reset_all() {
	$(".withdraw_button").removeClass().addClass("btn btn-outline-secondary").attr("disabled", false).html("Withdraw");
	$(".deposit_button").removeClass().addClass("btn btn-outline-primary").attr("disabled", false).html("Deposit");;
	$(".amount_input").val("").parent().hide();
}

function process_move(mode, pot_id, amount) {
	let data = {
		amount: amount,
		pot_id: pot_id
	};
	let url = "";
	if( mode == "deposit") {
		url = "deposit_pots.php";
	}else if( mode == "withdraw") {
		url = "withdraw_pots.php";
	}
	$.post(url, data, function(data) {
		if( data.status == 200 ) {
			let new_balance = data.new_balance;
			let new_balance_human = (new_balance/100).toLocaleString("en-GB", {style: "currency", currency: "GBP", minimumFractionDigits: 2});
			let pot_id = data.pot_id;
			$("span[pot_id='" + pot_id + "']").text(new_balance_human).addClass("text-info");
			reset_all();
		}else {
			let pot_id = data.pot_id;
			$("span[pot_id='" + pot_id + "']").addClass("text-danger").append(" - failed to " + mode);
			reset_all();
			$("button").removeClass().addClass("btn").attr("disabled", true).parent().hide();
		}
		$("#response_output").html(JSON.stringify(data, null, 2));
	}, "json");
}

