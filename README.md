# OpenCart_MemoryCoin
### ported from : https://github.com/btcgear/OpenCart_Bitcoin (version 1.4.0)

Initial bounty paid by cablepair.

This is an OpenCart payment module that communicates with a memorycoin client using JSON RPC.

This code accurately converts any Mt.Gox-compatible currency to MMC using the up-to-the-minute Mt.Gox values for average trade vaule and last trade value.  It is completely self contained and requires no cron jobs or external hardware other than a properly configured memorycoind server.  Every order creates a new memorycoin address for payment and gives it a label corresponding to the order_id of the order.  It installs like any other OpenCart plugin and it is completely integrated with OpenCart.

This extension has been tested with OpenCart versions between 1.5.2.1 and 1.5.5.1.


SS: 
	
	http://gyazo.com/2c1498e749859e096b4a292f7f6bf062.png
	http://gyazo.com/c1b2ca2a3d3d2adad9f39c516dac529d.png
	http://gyazo.com/658cec68c9b8ce9462e40b42bf0665b4.png
	http://gyazo.com/876ed1146f253baad05d5bc62d1713b6.png
	http://gyazo.com/40946afbabe5c2c5fb881ad0c76c1b3d.png
	http://gyazo.com/53c7ad17af52af39bec06a723cb849c4.png


# Dependencies

This extension now requires previous installation of [vQmod](https://code.google.com/p/vqmod/) and will not run properly without it. vQmod enables making changes to core OpenCart functionality without actually editing the core OpenCart files.

# Installation

1. Install vQmod.
2. Upload all files maintaining OpenCart folder structure.
3. Install the payment module in the admin console (Extensions > Payments > MemoryCoin > Install).
4. Edit the payment module settings (Extensions > Payments > MemoryCoin > Edit).
5. Run at least one test order through checkout up until payment (no payment required).  The first order initializes the MemoryCoin currency and will return 0 MMC for the order total.

## Explanation of Settings

* *MemoryCoin RPC Username*: This is the username in the "rpcuser" line of your memorycoin.conf file.
* *MemoryCoin RPC Host Address*: This is the IP address of the computer memorycoind is running on.
* *MemoryCoin RPC Password*: This is the password in the "rpcpassword" line of your memorycoin.conf file.
* *MemoryCoin RPC Port*: This is the port number in the "rpcport" line of your memorycoin.conf file.  The default port is 8332.
* *The prefix for the address labels*: The addresses will be assigned to accounts named with the format [prefix]_[order_id].
* *Is this a blockchain.info JSON-RPC server?*: Choose yes if connecting to blockchain.info JSON-RPC API.
* *Show MMC as a store currency*: If you select yes, your customers will be able to view prices in MMC.
* *Calculate MMC amount to this many decimal places*: Self explanatory. Choose the precision of the exchange rate calculation.
* *Time to complete order*: The number of seconds a customer has to send memorycoins to complete the order.
* *Status of a new order*: Choose a status for an order that has received payment with 0 confirmations.
* *Status*: Enable the MemoryCoin payment module here.
* *Sort Order*: Where you want this module to show up in relation to the other payment modules on the checkout page.
