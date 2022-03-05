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
		file_name:  feed_items.js
		function:	populate the feed_items page
*/
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

		$.post("create_feed_item.php", data, function(data) {
			let op = JSON.stringify(data, null, 2);
			$('#response_output').html("<pre class='text-start'>" + op + "</pre>");
		}, "json");
	});
});