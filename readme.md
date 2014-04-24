ALERT
==================
I've abandoned this plugin that implements DirectPayment towards using WebPayment. See (soon!) mangopay_edd_plugin_web on this github account. This is because I couldn't manage with Mangopay support to solve two issues that happens both when trying to update *CardRegistrations->RegistrationData* with token server data response. 

First error is an esporadic *500 - InternalError*, caused by too long data code; this is what I got from suport:

- We pay particular attention on this problem since and we got a response from our IT
team. They said that the stack is due to the update of RegistrationData (PUT on
/cardregistrations), the information is too big to be treated. So in our next API
update we will increase this field size.

Second error is *code 105206 - Registration data: invalid format*. For this error, Mangopay suport just skipped answering or giving any piece of information. As we say in spanish: que si quieres arroz Catalina.

So maybe in the future... please see WebPayment method implementation: https://github.com/aleph1888/mangopay_edd_plugin_web

mangopay_edd_plugin
==================

EasyDigitalDownloads Mangopay gateway (94% compatible with Campaignify)(Mangopay SDKv2)


Workflow
----
Once the plugin is installed, use it as any other edd payment gateway.

Configuration
--------------
1) Set gateway default values on *Campaings - Settings - Gateways*.

- Fields information:
	* Live API User / Live API Key => this is generated by Mangopay *client_creation.php*
	* Temp folder => This is MangopaySDKv2 requested temp folder.

Version
----
0.0

Tech
-----------
*Mangopay_EDD_gateway* uses a number of open source projects to work properly:
* [Wordpress.org] - https://github.com/WordPress/WordPress
* [Easy-Digital-Downloads] - https://github.com/easydigitaldownloads/Easy-Digital-Downloads
* [Astoundify / crowdfunding] - https://github.com/Astoundify/crowdfunding

Dependencies
--------------
- Mangopay SDKv2 (https://github.com/MangoPay/mangopay2-php-sdk

##### Calaways EDD gateways bundle.

* https://wiki.enredaos.net/index.php?title=COOPFUND-DEV#MANGOPAY


License
----
GPL


**Free Software, Hell Yeah!**
@BTC 1Evy47MqD82HGx6n1KHkHwBgCwbsbQQT8m

