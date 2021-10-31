# Change Log
All notable changes to this project is documented in this file.
## [0.4.0] - 

### Added
 - MultiPayment (Tas-him) Implementation
 - BossIPG (Basket Of ServiceS) Implementation

### Changes
 - Add `Mobile` and `Email` fields to `getToken()` method
 - Add SafeMode to check or not checking ssl certificate in curl
 - debug refund method


## [0.3.1] - 2021 15 Sept (namespace conflicts)
### Changes
 - namespace typo fixed
 - array to string conversion error fixed
 - Readme file updated

## [0.2.4] - 2021 14 Sept (RequestBuilder for json_decode)
### Changes
 - `RequestBuilder` bugfix for json_decode null associated argument bug
## [0.2.3] - 2021 14 Sept (RequestBuilder bugfix)
### Changes
 - `RequestBuilder` bugfix

## [0.2.2] - 2021 14 Aug (Refund URL bugfix)
### Changes
 - `URL_REFUND` address changed to correct value.


## [0.2.1] - 2021 12 Aug (Redirect Changes)
### Added
 - Added Versions `CHANGELOG.md` to this project.

### Changes
 - Output of `Pasargad->redirect()` method changed to send `URL` instead of `header()` manipulation
 - Updated `README.md` to make sure users consider this change  


## [0.1.0] - 2021 8 Aug  (init release)
### Added
 - RSAProcessor for Signing messages with RSA key
 - Implementing AbstractPayment Class
 - Implementing RequestBuilder for HTTP Request Handling using CURL
 - Development of business logic for merchant gateway management to Code 