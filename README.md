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
Contains the DB connect in ```$conn```
Contains a number of functions:
```send_data($conn, $key, $val)```



**index.php**
The landing page, which will provide information about the project and what will happen.
This will load:

**setup.php**
[ ] To merge with index.php in next version
This generates a link that can be sent to monzo to process the login process
Opens the monzo auth page:
```
  https://auth.monzo.com/?client_id=oauth2client_your_client_id&redirect_uri=your_redirect_uri&response_type=code&state=state_token
```
Once a user has logged in, an email will be sent to them, the link of which will be the above

**oauth.php**
This page is the landing page 
