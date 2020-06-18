<?php

$init = function($bootstrap) {

	$base = Core_Model_Directory::getBasePathTo("/app/local/modules/WalletMercadopagePS");
	# Register assets
	Nwicode_Assets::registerAssets("WalletMercadopagePS", "/app/local/modules/WalletMercadopagePS/resources/var/apps/");
	nwicode_assets::addjavascripts(array(
		"modules/walletmercadopageps/controllers/walletmercadopageps.js",
		"modules/walletmercadopageps/factories/walletmercadopageps.js",
	));	

};