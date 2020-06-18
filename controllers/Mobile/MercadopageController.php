<?php
class WalletMercadopagePS_Mobile_MercadopageController extends Application_Controller_Mobile_Default {

	/*
		Generate checkout data
	*/
	public function createformAction() {
		/*ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);	*/
		if ($params = Zend_Json::decode($this->getRequest()->getRawBody())) {
			$data['params'] = $params;
			$wallet = (new Wallet_Model_Wallet())->find($params['wallet_id']);
			if ($wallet->getId()) {

				$model = new WalletMercadopagePS_Model_PaymentMethodsMercadopage();
				$model->find(['wallet_id'=>$wallet->getId()]);
				if ($model->getId()) {

                    /* new - if instant pay  */
                    $bill = new Wallet_Model_Bill();
                    if ($params['wallet_bill_id']!="" && is_numeric($params['wallet_bill_id'])) {
                        // overwrite some data from bill
                        $bill->find($params['wallet_bill_id']);
                        if ($bill->getId()) {
                            $params['amount'] = $bill->getSumm();
                        }

                    }                    

					//Создадим запись в истории
					$history = new Wallet_Model_PaymentHistory();
					$history
						->setWalletId($wallet->getId())
						->setWalletCustomerId($params['wallet_customer_id'])
						->setSumm($params['amount'])
						->setCode($model->getData("original_title"))
                        ->setComplete(0)
                        ->setWalletBillId($bill->getId())
						->save();
				
				
					$data['mercado'] = $model->getData();
					$data['currency'] = Core_Model_Language::getCurrentCurrency()->getShortName();

				
					//Include library
					require_once($_SERVER['DOCUMENT_ROOT'].'/app/local/modules/WalletMercadopagePS/lib/vendor/autoload.php');
					MercadoPago\SDK::setAccessToken($model->getAccessToken());
					$preference = new MercadoPago\Preference();

					$goods = array();


					if ($bill->getId()) {
						$items = $bill->getItems();
						if ($items) {
							foreach($items as $i) {
								$item = new MercadoPago\Item();
								$item->id = "00001";
								$item->title = $i->getDescription(); 
								$item->quantity = $i->getQty();
								$item->unit_price = $i->getPrice();
								$goods[]=$item;	

							}

						} else {
							$item = new MercadoPago\Item();
							$item->id = "00001";
							$item->title = $bill->getDescription(); 
							$item->quantity = 1;
							$item->unit_price = $params['amount'];
							$goods[]=$item;						
						}
					} else {
						$item = new MercadoPago\Item();
						$item->id = "00001";
						$item->title = p__('walletmercadopageps','Deposit funds in the wallet') . " #" .$history->getId();  
						$item->quantity = 1;
						$item->unit_price = $params['amount'];
						$goods[]=$item;	
					}
					$preference->items = $goods;
					
					

					$preference->payment_methods = array(
						"installments" => (int)$model->getInstallments()
					);					


					$customer = $this->getSession()->getCustomer();
					$payer = new MercadoPago\Payer();
					$payer->email = $customer->getEmail();
					$payer->name = $customer->getFirstname();
					$payer->surname = $customer->getLastname();
					$preference->payer = $payer;

					$ipnUrl =parent::getUrl('walletmercadopageps/mobile_mercadopage/ipn', array('value_id' => $params['value_id'], 'wallet_id' => $params['wallet_id'],"wallet_customer_id"=>$params['wallet_customer_id'],"wallet_history_id"=>$history->getid(),'sb-token' => Zend_Session::getId()));
					
					//return all states to one address with different status
					$successUrl =parent::getUrl('walletmercadopageps/mobile_mercadopage/return', array('value_id' => $params['value_id'], 'wallet_id' => $params['wallet_id'],"wallet_customer_id"=>$params['wallet_customer_id'],"wallet_history_id"=>$history->getid(),'status'=>1,'sb-token' => Zend_Session::getId()));
					$failureUrl =parent::getUrl('walletmercadopageps/mobile_mercadopage/return', array('value_id' => $params['value_id'], 'wallet_id' => $params['wallet_id'],"wallet_customer_id"=>$params['wallet_customer_id'],"wallet_history_id"=>$history->getid(),'status'=>-1,'sb-token' => Zend_Session::getId()));
					$pendingUrl =parent::getUrl('walletmercadopageps/mobile_mercadopage/return', array('value_id' => $params['value_id'], 'wallet_id' => $params['wallet_id'],"wallet_customer_id"=>$params['wallet_customer_id'],"wallet_history_id"=>$history->getid(),'status'=>0,'sb-token' => Zend_Session::getId()));


					$preference->notification_url = $ipnUrl;
					$preference->back_urls = array(
						"success"=> $successUrl,
						"failure"=> $failureUrl,
						"pending"=> $pendingUrl
					);

					$preference->external_reference = $history->getid();
					$preference->auto_return = "approved";
					$preference->binary_mode = true;
					$preference->save(); # Save the preference and send the HTTP Request to create
					$data['success']=true;

					if ($model->getIsTesting()==1) {
						$data['payment_url']=$preference->sandbox_init_point;
						$history->setPaymentUrl($preference->sandbox_init_point)->setData('payment_id',$preference->id)->save();
					} else {
						$data['payment_url']=$preference->init_point;
						$history->setPaymentUrl($preference->init_point)->setData('payment_id',$preference->id)->save();
					}

					
						
				} else {
					$data = array('error' => 1, 'message' => p__('walletmercadopageps','An error occurred during process. Please try again later.'));
					$history->setComplete(-1)->save();
				}
			} else {
				$data = array('error' => 1, 'message' => p__('walletmercadopageps','An error occurred during process. Please try again later.'));
			}
			
		}else {
				$data = array('error' => 1, 'message' => p__('walletmercadopageps','An error occurred during process. Please try again later.'));
		}

		$this->_sendHtml($data);
	}



	public function returnAction() {
		$data = array();
		if ($params = $this->getRequest()->getParams()) {
			$data['params'] = $params;
			$history = new Wallet_Model_PaymentHistory();
			$history->find($params['wallet_history_id']);
			if ($history->getId()) {
				$this->_redirect('walletmercadopageps/mobile_mercadopage/result', array(
					'value_id' => $params['value_id'],
					'wallet_id' => $params['wallet_id'],
					'wallet_customer_id' => $params['wallet_customer_id'],
					'status' => $params['status'],
				));
			}

		}

	}


	//verification ipn request
	public function ipnAction() {
		$data = array();
		if ($params = $this->getRequest()->getParams()) {
			$data['params'] = $params;
			$ppp = array();
			$wallet = (new Wallet_Model_Wallet())->find($params['wallet_id']);
			$history = new Wallet_Model_PaymentHistory();
			$history->find($params['wallet_history_id']);
			if ($wallet->getId() && $history->getId() && $params['type']=="payment") {
				
				$model = new WalletMercadopagePS_Model_PaymentMethodsMercadopage();
				$model->find(['wallet_id'=>$wallet->getId()]);

				if ($model->getId()) {
					
					//Include library
					require_once($_SERVER['DOCUMENT_ROOT'].'/app/local/modules/WalletMercadopagePS/lib/vendor/autoload.php');
					MercadoPago\SDK::setAccessToken($model->getAccessToken());	
					$payment = MercadoPago\Payment::find_by_id($params["data_id"]);
					if ($payment->status == 'approved' && (float)$payment->transaction_amount>=(float)$history->getSumm()){
						$wallet_customer = (new Wallet_Model_Customer())->find($history->getWalletCustomerId());
						$history->setComplete(1)->save();
						$wallet_customer->addTransaction($history->getSumm(),$model->getData("original_title")." - ".p__('walletmercadopageps','Deposit funds in the wallet'). " #" .$history->getId(),'in',0,$wallet_customer->getId());
                        if ($history->getWalletBillId()!="" && is_numeric($history->getWalletBillId()) && $history->getWalletBillId()>0) {
                            $wallet_customer->acceptBill($history->getWalletBillId());
                        } 
						
						
					}
				}
				
			}
			
		

			
			/*$fp = fopen($_SERVER['DOCUMENT_ROOT'] . "/myText.txt","a");
			fwrite($fp,print_r($params,true));
			fwrite($fp,print_r($_POST,true));
			fwrite($fp,print_r($payment,true));
			fclose($fp);*/
		}

	}
	

}