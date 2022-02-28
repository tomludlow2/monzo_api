# monzo_api

A monzo api integration from the offical [monzo docs](https://docs.monzo.com/#introduction) 

This file outlines the workflow from the user opening the app, through to using the components. 
This is a work in progress, and should only be enabled on a hardened server due to the nature of the content.

## Overview
- This api uses OAuth 2.0 to connect to the API and verify credentials
- There is a database that holds both your API's information, as well as the user information when the user registers for the first time
- The registration requires the user to visit your API -> Redirects to Monzo -> Emails user -> User verifies
- At this point you have a token that you can make requests with
- This token does not permit you to do anything specific at that stage
- Instead the user will be pinged from the Monzo App and will have to put in their PIN to get your system access

## Setup
- You will need a Monzo Account (obviously
- You will need a database (I am using a MySQL database)
- [ ] I will try and copy a blank example SQL insert **later**


## Specific Files
I have tried to organise this in the order in which they will run. 


## Authentication and Connection
**conn.php**
- Contains the DB connect in ```$conn```
- Contains a number of functions:
- ```send_data($conn, $key, $val)``` - Stores in DB
- ```get_data($conn, $key)``` - Retrieves from DB


**index.php**
- The landing page, which will provide information about the project and what will happen.
- ~~updated from old setup.php~~
- Generates a link for monzo that initiates the setup process
- This is in the form of:
```
  https://auth.monzo.com/?client_id=oauth2client_your_client_id&redirect_uri=your_redirect_uri&response_type=code&state=state_token
```
- Once a user has logged in, an email will be sent to them, the link of which will be the above

**oauth.php**
- This page is the receipt for the token information from monzo once the user has received the email
- Note that for further development this page **may** be served on a different device
- - This may be due to email arriving to user's phone 
- Generates a link to the next page

**get_access_token.php**
- Posts a variety of information to Monzo
```
$grant_type,   $client_id,   $client_secret,   $redirect_uri,   $code
```
- Note that the redirect_uri is standard across the api, but is not specifically needed
- Receives a json_enocded object:
```
{
    "access_token": "access_token",
    "client_id": "client_id",
    "expires_in": 21600,
    "refresh_token": "refresh_token",
    "token_type": "Bearer",
    "user_id": "user_id"
}
```
- These are checked into 4 success points for this application:
- All 4 are required in order to proceed
- Once all 4 are correct, generates a link to the next page


**whoami.php**
- Checks the stored authentication token against the Monzo API
- Sends the access token as a Authorisation: Bearer
- If the token is authorised, will display this as a badge
- It token is invalid - requires redirection to index to restart process

**refresh_access_token.php**
- Very similar opeartion to get_access_token (see above) but changes refresh token instead of temporary token
- Updated styling
- Redirects to hub once completed


### Basic Parameters
**accounts.php**
- This function has various functions
- It simply first calls the monzo API using the information stored in the database
- If an account is returned, it will generate a specific page
- If called with ```?format=json``` then it will output the information in json
- If called with ```?store=0``` then it will not push the data to the server (Useful for comparing old->new data)
- The HTML version allows you to display both a tabulated and json version of the data side by side

**balance.php**
- This function has various functions
- It simply first calls the monzo API using the information stored in the database
- If balances are returned, it will generate a specific page
- If called with ```?format=json``` then it will output the information in json
- If called with ```?store=0``` then it will not push the data to the server (Useful for comparing old->new data)
- The HTML version allows you to display both a tabulated and json version of the data side by side


### Pots - View, Deposit and Withdraw
**pots.php**
- This function has various functions
- It simply first calls the monzo API using the information stored in the database
- If pots are returned, it will generate a specific page
- If called with ```?format=json``` then it will output the information in json
- If called with ```?store=0``` then it will not push the data to the server (Useful for comparing old->new data)
- When formatted as a page, it generates a series of Bootstrap cards, one for each pot, aligning on default row/col structure
- You can also add ```?show_deleted``` to show any deleted pots (will affect both json and page modes)
- This function also gives options to move money, either to withdraw or deposit, using the following
- (JS is with ```assets/pots.js```);

**deposit_pots.php**
- Accepts ```?pot_id=xxx  &   amount=100``` amount in pence 
- Generates an output, look for ```status=200``` for it going through with ```new_balance```
- Throws some generic errors where possible

**withdraw_pots.php**
- Accepts ```?pot_id=xxx  &   amount=100``` amount in pence 
- Generates an output, look for ```status=200``` for it going through with ```new_balance```
- Throws some generic errors where possible

### Transactions
**generate_transaction_table.php**
- This includes a function that generates a table to show any transaction objects which are passed
- TH includes an in/out column rather than pure amount to display in a more consistent style
- [ ] To do - Filter table javascript (coming)
- Merchant and Transaction IDs are shortened and if **Bootstrap** and **popper.js** are included then tooltips display the full amount, and this requires ```transactions.js``` to activate this. Double clicking the table makes it small
- Errors in storage are reported in a tooltip

**new_transactions.php**
- This calls the monzo API, getting any new transactions since the last time that it was stored
- This function can be called with ```?store=0``` to not push the transactions to the server
- If this function is called with either ```?store=undefined or ?store=1``` then all subsequent runs on this function will return 0 rows as they will have been stored. 
- If called with ```?format=json``` it will output the information in json for use elsewhere in the application
- For testing / debuggin purposes, you can send ```?test_run``` and once you have set ```$YOUR_TEST_TRANS_ID``` on line ```4``` then it will send this, suggest picking one around 20 transactions earlier.

**recent_transactions.php**
- This function queries the monzo API for any recent transactions, the monzo API limits this to 90 days
- This defaults to the last 7 days
- Calling with ```?time_filter=7_d``` would be the equivalent.
- The expected format is ```number-of-units``` ```_``` ```h|d|w|m``` for hours, days, weeks, months]
- It will throw a readable error in ```filter_error``` if you supply an invalid or too high range (2160h, 90d, 12w, 3m).
- Note that 3 months is a crude 3 months, if this takes it over 90 days it wont return anything - days is best.  
- Similarly to ```new_transactions.php``` and other files above, you can pass: ```?format=json|page & store=0|1```
- This then calls the same ```generate_transaction_table.php``` as before, to generate a table, or returns the json

**all_transactions.php**
- This function queries monzo for the entirity of a user's transactions
- This can only be done within the first 5 minutes of authorising access to the account
- You will receive a permissions issue if it is outside this window
- You can pass ```?format=json|page``` to dictate if it displays the transactions
- Note that it will automatically store all these transactions in the transactions table. 
- This then calls the same ```generate_transaction_table.php``` as before, to generate a table, or returns the json


### Feeds
**setup_feed_item.php**
- This file allows the user to create a Feed Item in their feed which will display alongside other transactions
- It is a bootstrap form that sends data via $.post(AJAX) to ```create_feed_item.php``` which sends back some json response data
- The feed allows the customisation of message title and message body (both mandatory), as well as image_url (to appear where merchant icons appear) (with a default), as well as a target_url option (can be any url - will be validated before submission), and a background and title colour for the message
- Submission the updates the raw JSON in the right hand panel
- The JS for this is in ```feed_items.js```


### Receipts
**receipt_management.php**
- This function essentially houses a Bootstrap / jQuery AJAX front-end for the receipt functions. 
- Monzo accepts a json receipt that can include items, with subitems, as well as tax info, payments, and merchant information
- Currently this framework allows you to submit items (not subitems yet), and automatically inserts 0 tax with a VAT = 0 receipt in order for monzo to accept it
- Each receipt has a unique receipt ID, see inside ```add_receipt.php``` to create a custom prefix for your receipts, but it defaults to include the transaction ID, as this must be unique.
- Receipt management requires the following functions (some of this is a bit clunky as it developed piecemeal)
- The JS is contained in ```assets/receipts.js```
- **IMPORTANT**
- Currently the monzo [Delete receipt API endpoint](https://docs.monzo.com/#delete-receipt) does not appear to work, despite calling it in the manner advertised on the monzo docs, I cannot get any other endpoint than insufficent permissions. 
- If anyone is able to help, let me know. 


**Delete Receipt**
- - This essentially makes a call to ```null_receipt.php``` to generate a blank receipt
- - It prompts the user to ensure they know that this won't fully remove the receipt
**Validate Receipt**
- - Ideally there should not be a circumstance where the remote receipt information is incorrect, however given the application stores a copy locally too, it is prudent to have a mechanism to check this
**null_receipt.php**
- As a workaround for the broken Delete Receipt API, this function simply overwrites an existing receipt with:
```
Transaction Value = £xx.yy
Tax = 0
Payment = Card
```
- Simply pass a receipt ID (in full, following whatever nomenclature you use)

**populate_receipt_page.php**
- As receipt management is dynamic, it uses AJAX to populate itself on load rather than CGI pre-load
- This function produces a JSON encoded readout of the most recent transactions and receipts. 
- Pass ```$_POST['limit']``` to set the limit for both. 

**get_receipt.php**
- This file calls the monzo API to collect receipt data from an allocated ```external_id``` number (which you need a copy of).
- It then simply returns this to the caller
- Pass this as a POST variable receipt_id

### Webhooks
**register_webook.php**
- From [Webooks](https://docs.monzo.com/#webhooks)
- Send a request GET or POST with ```endpoint=https://yourdomain.tld/your_endpoint``` which will register the endpoint
- Look for ```status:200``` in the JSON output to validate success

**list_webhooks.php**
- This file will list the webhooks associated with the account and write them as bootstrap cards.
- Can be called with ```?format=json``` to output the relevant webhooks in JSON format
- On the ```?format=page``` you can add a webhook, view the existing webhooks, and delete these too

**delete_webhook.php**
- This sends a delete request to monzo for the ```?webhook_id=xxx``` parameter
- It generates a similar screen to ```list_webhooks.php``` if ```?format=page```, or alternatively pass ```?format=json``` to get a json output
- Look for ```status:200``` in the JSON output to validate success

**webhook_handler.php**
- This is an example of how you might handle a webhook request

