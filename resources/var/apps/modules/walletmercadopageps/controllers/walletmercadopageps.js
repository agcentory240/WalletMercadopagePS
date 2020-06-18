App.config(function($stateProvider, HomepageLayoutProvider) {

    $stateProvider.state('walletmercadopageps-payment', {
        url: BASE_PATH+"/walletmercadopageps/mobile_walletmercadopage/find/value_id/:value_id/wallet_id/:wallet_id/wallet_customer_id/:wallet_customer_id/amount/:amount",
        controller: 'WalletMercadopagePS',
        templateUrl: "modules/walletmercadopageps/templates/l1/payment.html"
    });
    $stateProvider.state('walletmercadopageps-payment-instant', {
        url: BASE_PATH+"/walletmercadopageps/mobile_walletmercadopage/find/value_id/:value_id/wallet_id/:wallet_id/wallet_customer_id/:wallet_customer_id/amount/:amount/bill_id/:bill_id",
        controller: 'WalletMercadopagePSController',
        templateUrl: "modules/walletmercadopageps/templates/l1/payment.html"
    });      
    $stateProvider.state('walletmercadopageps-payment-result', {
        url: BASE_PATH+"/walletmercadopageps/mobile_mercadopage/result/value_id/:value_id/wallet_id/:wallet_id/wallet_customer_id/:wallet_customer_id/status/:status",
        controller: 'WalletMercadopagePSResultController',
        templateUrl: "modules/walletmercadopageps/templates/l1/payment.html"
    });	

}).controller('WalletMercadopagePSController', function ($scope, $state, $stateParams,$timeout, $translate, WalletMercadopagePSFactory,Loader,$window, Dialog,$ionicHistory) {
    
	console.log("WalletMercadopagePSController fired!");
	
	$scope.data = {};
	WalletMercadopagePSFactory.value_id = $stateParams.value_id;
	$scope.value_id = $stateParams.value_id;
	$scope.data.value_id = $stateParams.value_id;
	$scope.data.amount = $stateParams.amount;
	$scope.data.wallet_id = $stateParams.wallet_id;
    $scope.data.wallet_customer_id = $stateParams.wallet_customer_id;
    $scope.data.wallet_bill_id = $stateParams.bill_id;
    $scope.is_loading = true;
	$scope.old_style = true;
	console.log("WalletMercadopagePS:");
	console.log($scope.data);
	Loader.show();
	WalletMercadopagePSFactory.CreateForm($scope.data).then(function (formData) {
		console.log("WalletMercadopagePSFactory.CreateForm return:");
		console.log(formData);
		Loader.hide();
		if (formData.success) $window.location = formData.payment_url;
		else {
			Dialog.alert($translate.instant("Error"), formData.error_description,"OK") .then(function () {
				$ionicHistory.nextViewOptions({
					historyRoot: true,
					disableAnimate: false
				});
			$state.go('home');
			});
		}

	}, function (error_data) {
		console.log("WalletMercadopagePSFactory.CreateForm return ERROR:");
		console.log(error_data);	
		Loader.hide();
		Dialog.alert($translate.instant("Error"), error_data.message,"OK");
		$ionicHistory.nextViewOptions({
			historyRoot: true,
			disableAnimate: false
		});
		$state.go('home');
	});	

}).controller('WalletMercadopagePSResultController', function ($scope, $state, $stateParams,$timeout, WalletMercadopagePSFactory,Loader,$window, Dialog,HomepageLayout, $ionicHistory) {
     angular.extend($scope, {
        value_id: $stateParams.value_id,
        layout: HomepageLayout
    });   
	console.log("WalletMercadopagePSResultController fired!");
	
	$scope.data = {};
	WalletMercadopagePSFactory.value_id = $stateParams.value_id;
	$scope.value_id = $stateParams.value_id;
	$scope.data.value_id = $stateParams.value_id;
	$scope.data.status = $stateParams.status;
	$scope.data.wallet_id = $stateParams.wallet_id;
	$scope.data.wallet_customer_id = $stateParams.wallet_customer_id;
    $scope.is_loading = false;
	$scope.old_style = true;	
	console.log("WalletMercadopagePSResultController:");
	console.log($scope.data);
	
	$scope.closeWindow = function() {
		$ionicHistory.nextViewOptions({
			historyRoot: true,
			disableAnimate: false
		});	
		$state.go('home').then(function () {
			$state.go('wallet-view', {
				value_id: $scope.value_id
			});
		});		
	
	}

});