$(function() {
	$('#create_feed_item').click(function(event) {
		//Send the request

		let title = $('#feed_title').val();
		let body = $('#feed_body').val();
		let image_url = $('#feed_image_url').val();
		let background_colour = $('#feed_bg_colour').val();
		let text_colour = $('#feed_text_colour').val();
		let target_url = $('#feed_target_url').val();


		let data = {
			title: title,
			body: body,
			image_url: image_url,
			background_colour: background_colour,
			text_colour: text_colour,
			target_url: target_url
		};

		$.post( "create_feed_item.php", data, function(data) {
			let op = JSON.stringify(data, null, 2);
			$('#response_output').html("<pre class='text-start'>" + op + "</pre>");
		}, "json");
	});
});