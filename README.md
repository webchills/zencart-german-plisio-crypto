# ZenCart Plisio Plugin

Accept cryptocurrency payments in your ZenCart store with [Plisio](https://plisio.net/zencart-accept-crypto) - our fully automated payment processing and invoice system makes it easy, convenient, and risk-free for you and your customers.

With a simple installation of the Plisio ZenCart extension in your store's checkout, customers can pay for your goods and services with cryptocurrencies like Bitcoin, Litecoin, Ethereum, Bitcoin Cash, Monero and other altcoins.

### Features:
* The gateway is fully automatic – set it and forget it.
* Receive automatic payment confirmations and order status updates.
* Set your prices in any local fiat currency, and the payment amount in cryptocurrency will be calculated using real-time exchange rates.
* No setup or recurring fees.
* No chargebacks – guaranteed!

### Functionality:
* Extend invoice expiration time up to 72 hours.
* Accept slight underpayments automatically.
* Refunds can be issued directly from the invoice and without the involvement of the seller.

### How it works - an example:
1. An item in the store costs 100 euro.
2. A customer wants to buy the item and selects to pay with Bitcoin.
3. An invoice is generated and, according to the current exchange rate, the price is 10000 euro per bitcoin, so the customer has to pay 0.01 bitcoins.
4. Once the invoice is paid, the merchant receives 99.5 euro (100 euro minus our 0,5% flat fee), or 0.0099 BTC.

Any questions? Write to our support team at [support@plisio.net](mailto:support@plisio.net)

## Installation

Sign up for a Plisio account at [https://plisio.net](https://plisio.net).

#### via FTP

1. Download [plisio_zencart.zip](https://github.com/Plisio/zencart-plugin/releases/download/v1.0.0/plisio_zencart.zip).
2. Extract downloaded zip. Upload includes directory and files to the root directory of your ZenCart installation.
3. Login to your ZenCart admin panel and go to Modules » Payment. Click on Plisio and click Install.
4. Set Enable Plisio module to True. Enter [API Secret key](https://plisio.net/faq/how-to-connect-the-api) and configure other extension settings. Click Update.

To create new order statuses login to your ZenCart admin panel, go to Localization » Orders Status, click Insert, enter new order status name and click Insert.