# monzo_api

A monzo api integration from the offical [monzo docs](https://docs.monzo.com/#introduction) 

This file give the workflow from user opening the app, through to using the components. 
This is a work in progress, and should only be enabled on a hardened server due to the nature of the content.

## Overview



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
- ```
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
- ```
$grant_type,   $client_id,   $client_secret,   $redirect_uri,   $code
```
- Note that the redirect_uri is standard across the api, but is not specifically needed
- Receives a json_enocded object:
- ```
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

