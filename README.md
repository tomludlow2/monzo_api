# monzo_api

A monzo api integration from the offical [monzo docs](https://docs.monzo.com/#introduction) 

This file give the workflow from user opening the app, through to using the components. 
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

**accounts.php*
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

**pots.php**
- This function has various functions
- It simply first calls the monzo API using the information stored in the database
- If pots are returned, it will generate a specific page
- If called with ```?format=json``` then it will output the information in json
- If called with ```?store=0``` then it will not push the data to the server (Useful for comparing old->new data)
- When formatted as a page, it generates a series of Bootstrap cards, one for each pot, aligning on default row/col structure
- You can also add ```?show_deleted``` to show any deleted pots (will affect both json and page modes)
