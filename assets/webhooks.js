const TEST_TRANSACTION = {
    "type": "transaction.created",
    "data": {
        "account_id": "acc_00008gju41AHyfLUzBUk8A",
        "amount": -350,
        "created": "2015-09-04T14:28:40Z",
        "currency": "GBP",
        "description": "Ozone Coffee Roasters",
        "id": "tx_00008zjky19HyFLAzlUk7t",
        "category": "eating_out",
        "is_load": false,
        "settled": "2015-09-05T14:28:40Z",
        "merchant": {
            "address": {
                "address": "98 Southgate Road",
                "city": "London",
                "country": "GB",
                "latitude": 51.54151,
                "longitude": -0.08482400000002599,
                "postcode": "N1 3JD",
                "region": "Greater London"
            },
            "created": "2015-08-22T12:20:18Z",
            "group_id": "grp_00008zIcpbBOaAr7TTP3sv",
            "id": "merch_00008zIcpbAKe8shBxXUtl",
            "logo": "https://pbs.twimg.com/profile_images/527043602623389696/68_SgUWJ.jpeg",
            "emoji": "🍞",
            "name": "The De Beauvoir Deli Co.",
            "category": "eating_out"
        }
    }
}

$(function() {

	$('#webhook_endpoint').on("change", function(event) {
		//Check validation
		let val = $(this).val();

		if( check_url(val) ) {
			let url = new URL(val);
			if( url.protocol == "https:") {
				$('#register_webhook_btn').addClass("btn-success").removeClass("btn-warning").attr("disabled", false).html("Register Webhook");
			}else {
				$('#register_webhook_btn').addClass("btn-warning").removeClass("btn-success").html("Invalid Endpoint (not https)");
			}
		}else {
			$('#register_webhook_btn').addClass("btn-warning").removeClass("btn-success").html("Invalid Endpoint");
		}
		console.log(check_url(val));
		console.log(new URL(val));

	});

	$('#register_webhook_btn').click(function(event) {
		$('#response_output').removeClass("bg-success").removeClass("bg-danger");
		let val = $('#webhook_endpoint').val();
		let url = new URL(val);
		let endpoint = url.origin;

		$.post("register_webhook.php", {endpoint:endpoint}, function(data) {
			if( data.status == 200) {
				$('#response_output').html("<pre class='text-start'>" + JSON.stringify(data, null,2) + "</pre>").addClass("bg-success");
			}else{
				$('#response_output').html("<pre class='text-start'>" + JSON.stringify(data, null,2) + "</pre>").addClass("bg-danger");
			}
		}, "json");
	});

	$(".test_webhook").click(function(event) {
		$('#response_output').removeClass("bg-success").removeClass("bg-danger");
		event.preventDefault();
		let id = $(this).prop("id");
		let href = $(this).prop("href");
		console.log(id, href);

		let conf = confirm("Confirm you would like to send a test transaction to your webhook endpoint");

		let send_data = new Object();
		send_data.name = "test";
		send_data.format = "json";
		if( conf ) {
			console.log(TEST_TRANSACTION);
			//console.log(TEST_TRANSACTION.serialize());
			//console.log(JSON.stringify(send_data));
			console.log(href);
			$.post(href,{data: TEST_TRANSACTION}, function(data){
				console.log(data);				
				$('#response_output').html("<pre class='text-start'>" + JSON.stringify(data, null,2) + "</pre>").addClass("bg-success");
			}, "json");
		}else {

		}
	});

	$(".delete_webhook").click(function(event) {
		$('#response_output').removeClass("bg-success").removeClass("bg-danger");
		event.preventDefault();
		let id= $(this).prop("id");
		let conf = confirm("Confirm you would like to delete this webhook");
		if( conf ) {
			$.post("delete_webhook.php", {webhook_id:id,format:"json"}, function(data) {
				console.log(data);
				if( data.delete_outcome == 200 ) {
					//It has been deleted
					let w_id = data.webhook_id;
					$("#holder_" + w_id).remove();
					$('#response_output').html("<pre class='text-start'>" + JSON.stringify(data, null,2) + "</pre>").addClass("bg-success");
				}else {
					$('#response_output').html("<pre class='text-start'>" + JSON.stringify(data, null,2) + "</pre>").addClass("bg-danger");
				}
				

			}, "json");
		}else {

		}
	});
	
});

function check_url(string) {
  let url;
  
  try {
    url = new URL(string);
  } catch (_) {
    return false;  
  }

  return url.protocol === "http:" || url.protocol === "https:";
}