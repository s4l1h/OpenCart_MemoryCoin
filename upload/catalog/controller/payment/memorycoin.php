<?php
/*
Copyright (c) 2013 John Atkinson (jga)
*/

class ControllerPaymentMemoryCoin extends Controller {

    private $payment_module_name  = 'memorycoin';
	protected function index() {
        $this->language->load('payment/'.$this->payment_module_name);
    	$this->data['button_memorycoin_pay'] = $this->language->get('button_memorycoin_pay');
    	$this->data['text_please_send'] = $this->language->get('text_please_send');
    	$this->data['text_mmc_to'] = $this->language->get('text_mmc_to');
    	$this->data['text_to_complete'] = $this->language->get('text_to_complete');
    	$this->data['text_click_pay'] = $this->language->get('text_click_pay');
    	$this->data['text_uri_compatible'] = $this->language->get('text_uri_compatible');
    	$this->data['text_click_here'] = $this->language->get('text_click_here');
    	$this->data['text_pre_timer'] = $this->language->get('text_pre_timer');
    	$this->data['text_post_timer'] = $this->language->get('text_post_timer');
		$this->data['text_countdown_expired'] = $this->language->get('text_countdown_expired');
    	$this->data['text_if_not_redirect'] = $this->language->get('text_if_not_redirect');
		$this->data['error_msg'] = $this->language->get('error_msg');
		$this->data['error_confirm'] = $this->language->get('error_confirm');
		$this->data['error_incomplete_pay'] = $this->language->get('error_incomplete_pay');
		$this->data['memorycoin_countdown_timer'] = $this->config->get('memorycoin_countdown_timer');
		$memorycoin_mmc_decimal = $this->config->get('memorycoin_mmc_decimal');
				
		$this->checkUpdate();
	
        $this->load->model('checkout/order');
		$order_id = $this->session->data['order_id'];
		$order = $this->model_checkout_order->getOrder($order_id);

		$current_default_currency = $this->config->get('config_currency');
		$this->data['memorycoin_total'] = sprintf("%.".$memorycoin_mmc_decimal."f", round($this->currency->convert($order['total'], $current_default_currency, "MMC"),$memorycoin_mmc_decimal));
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET memorycoin_total = '" . $this->data['memorycoin_total'] . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

		require_once('jsonRPCClient.php');
		
		$memorycoin = new jsonRPCClient('http://'.$this->config->get('memorycoin_rpc_username').':'.$this->config->get('memorycoin_rpc_password').'@'.$this->config->get('memorycoin_rpc_address').':'.$this->config->get('memorycoin_rpc_port').'/');
		
		$this->data['error'] = false;
		try {
			$memorycoin_info = $memorycoin->getinfo();
		} catch (Exception $e) {
			$this->data['error'] = true;
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/memorycoin.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/memorycoin.tpl';
			} else {
				$this->template = 'default/template/payment/memorycoin.tpl';
			}	
			$this->render();
			return;
		}
		$this->data['error'] = false;
		
		$this->data['memorycoin_send_address'] = $memorycoin->getaccountaddress($this->config->get('memorycoin_prefix').'_'.$order_id);
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET memorycoin_address = '" . $this->data['memorycoin_send_address'] . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/memorycoin.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/memorycoin.tpl';
		} else {
			$this->template = 'default/template/payment/memorycoin.tpl';
		}	
		
		$this->render();
	}
	
	
	public function confirm_sent() {
        $this->load->model('checkout/order');
		$order_id = $this->session->data['order_id'];
        $order = $this->model_checkout_order->getOrder($order_id);
		$current_default_currency = $this->config->get('config_currency');	
		$memorycoin_mmc_decimal = $this->config->get('memorycoin_mmc_decimal');	
		$memorycoin_total = $order['memorycoin_total'];
		$memorycoin_address = $order['memorycoin_address'];
			require_once('jsonRPCClient.php');
			$memorycoin = new jsonRPCClient('http://'.$this->config->get('memorycoin_rpc_username').':'.$this->config->get('memorycoin_rpc_password').'@'.$this->config->get('memorycoin_rpc_address').':'.$this->config->get('memorycoin_rpc_port').'/');
		
			try {
				$memorycoin_info = $memorycoin->getinfo();
			} catch (Exception $e) {
				$this->data['error'] = true;
			}

		try {
			$received_amount = $memorycoin->getreceivedbyaddress($memorycoin_address,0);
		
			if(round((float)$received_amount,$memorycoin_mmc_decimal) >= round((float)$memorycoin_total,$memorycoin_mmc_decimal)) {
				$order = $this->model_checkout_order->getOrder($order_id);
				$this->model_checkout_order->confirm($order_id, $this->config->get('memorycoin_order_status_id'));
				echo "1";
			}
			else {
				echo "0";
			}
		} catch (Exception $e) {
			$this->data['error'] = true;
			echo "0";
		}
	}
	
	public function checkUpdate() {
		if (extension_loaded('curl')) {
			$data = array();
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency WHERE code = 'MMC'");
						
			if(!$query->row) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "currency (title, code, symbol_right, decimal_place, status) VALUES ('MemoryCoin', 'MMC', ' MMC', ".$this->config->get('memorycoin_mmc_decimal').", ".$this->config->get('memorycoin_show_mmc').")");
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency WHERE code = 'MMC'");
			}
			
			$format = '%Y-%m-%d %H:%M:%S';
			$last_string = $query->row['date_modified'];
			$current_string = strftime($format);
			$last_time = strptime($last_string,$format);
			$current_time = strptime($current_string,$format);
		
			$num_seconds = 60; //every [this many] seconds, the update should run.
			
			if($last_time['tm_year'] != $current_time['tm_year']) {
				$this->runUpdate();
			}
			else if($last_time['tm_yday'] != $current_time['tm_yday']) {
				$this->runUpdate();
			}
			else if($last_time['tm_hour'] != $current_time['tm_hour']) {
				$this->runUpdate();
			}
			else if(($last_time['tm_min']*60)+$last_time['tm_sec'] + $num_seconds < ($current_time['tm_min'] * 60) + $current_time['tm_sec']) {
				$this->runUpdate();
			}
		}
	}
	
	public function runUpdate() {

	 
		// our curl handle (initialize if required)
		$ch = null;
		if (is_null($ch)) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; OpenCart MMC PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		}
		curl_setopt($ch, CURLOPT_URL, 'http://s4l1h.github.io/mmc/ticker.json');
	 
		// run the query
		$res = curl_exec($ch);
		if ($res === false) throw new Exception('Could not get reply: '.curl_error($ch));
		$dec = json_decode($res, true);
		if (!$dec) throw new Exception('Invalid data received, please make sure connection is working and requested API exists');
		$mmcdata = $dec;
		
		$currency = "MMC";
		$value=$mmcdata['ask_usd'];
		/*
		$avg_value = $mmcdata['return']['avg']['value'];
		$last_value = $mmcdata['return']['last']['value'];
				*/
		/*if ((float)$avg_value && (float)$last_value) {
			if($avg_value < $last_value) {
				$value = $avg_value;
			}
			else {
				$value = $last_value;
			}*/
			$value = 1/$value;
			$this->db->query("UPDATE " . DB_PREFIX . "currency SET value = '" . (float)$value . "', date_modified = '" .  $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE code = '" . $this->db->escape($currency) . "'");
		/*}
		
		$this->db->query("UPDATE " . DB_PREFIX . "currency SET value = '1.00000', date_modified = '" .  $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE code = '" . $this->db->escape($this->config->get('config_currency')) . "'");
		*/
		$this->cache->delete('currency');
	}
}
?>
