<?php

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
			file_name:  generate_transaction_table.php
			function:	Builds the transaction table used in app
			arguments (default first):	
				nil
	*/
	
	//Requires conn to create the GENERATE_TABLE_RAW_POTS
	function transaction_table($transactions) {
		$pots_lookup = [];
		global $GENERATE_TABLE_RAW_POTS;
		foreach($GENERATE_TABLE_RAW_POTS as $pot) {
			$pots_lookup[$pot->pot_id] = $pot->pot_name;
		}

		$r = "<table id='transaction_table' class='table text-start table-hover'><thead class='thead-dark'><tr>";
		$headings = ["ID", "Date Created", "Date Settled", "Amount-In", "Amount-Out", "Description", "Merchant", "Category", "Notes", "DB"];
		$headings_ids = ['transaction_id', 'date_created', 'date_settled', 'amount_in', 'amount_out', 'description', 'merchant_id', 'category', 'notes', 'stored'];
		foreach($headings as $key => $val) {
			$r .= "<th scope='col'><span class='transaction_table_header' id='filter_" . $headings_ids[$key] . "'>$val</span></th>\n";
		}
		$r .= "</tr></thead>\n<tbody>";
		if( count($transactions) > 0 ) {
			foreach($transactions as $t) {
				$r .= "<tr><th scope='row'><span class='transaction_id' id='" . $t['transaction_id'] . "' data-bs-toggle='tooltip' data-bs-placement='top' title='" . $t['transaction_id'] . "'>tx"; 
				$short_id = substr($t['transaction_id'],-4);
				$r .= "...$short_id</span></th>";
				$r .= "<td>" . $t['date_created'] . "</td>";
				$r .= "<td>" . $t['date_settled'] . "</td>";

				if($t['amount']>=0) {
					$r .= "<td><span class='transaction_amount postitive_amount'>&pound;" . number_format(($t['amount']/100),2) . "</span></td><td></td>";
				}else {
					$t['amount'] *= -1;
					$r .= "<td></td><td><span class='transaction_amount negative_amount'>&pound;" . number_format(($t['amount']/100),2) . "</span></td>";
				}

				//   ^pot_[a-zA-Z0-9]{22}
				if( preg_match('/^pot_[a-zA-Z0-9]{22}/', $t['description']) ) {
					$r .= "<td>" . "<img src='assets/icons/piggy-bank.svg' height='20px' /> - ";
					$r .= $pots_lookup[$t['description']] . "</td>";
				}else {
					$r .= "<td><span class='transaction_description'>" . $t['description'] . "</span></td>";
				}

				if( $t['merchant_id'] != "") {
					$short_merch_id = substr($t['merchant_id'],-4);
					$r .= "<td><span class='transaction_merchant_id' id='" . $t['merchant_id'] . "'data-bs-toggle='tooltip' data-bs-placement='top' title='" . $t['merchant_id'] . "'>mx...$short_merch_id</span></td>";
				}else {
					$r .= "<td><span class='transaction_merchant_id'>&nbsp;</span></td>";
				}
				$r .= "<td><span class='transaction_category'>" . $t['category'] . "</span></td>";
				$r .= "<td><span class='transaction_notes'>" . $t['notes'] . "</span></td>";
				if($t['stored'] == 1 ) {
					$r .= "<td><img src='assets/icons/check-square.svg' height='20px' /></td>";
				}else {
					$rf = $t['reason_failed'];
					$r .= "<td><img data-bs-toggle='tooltip' data-bs-placement='top' title='$rf' src='assets/icons/x-square.svg' height='20px' /></td>";
				}
				
				$r .= "</tr>";
			}
		}else {
			$r .= "<tr><th scope='row'>--</th>";
			$r .= "<td colspan='9'>No New Transactions since last database sync</td></tr>";
		}
		$r .= "</tbody></table>";
		return $r;
	}


?>