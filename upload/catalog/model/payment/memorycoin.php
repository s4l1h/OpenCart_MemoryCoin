<?php 
/*
Copyright (c) 2013 John Atkinson (jga)
*/

class ModelPaymentMemoryCoin extends Model {
  	public function getMethod($address) {
		$this->load->language('payment/memorycoin');
		
		if ($this->config->get('memorycoin_status')) {
        	$status = TRUE;
		} else {
			$status = FALSE;
		}
		
		$method_data = array();
	
		if ($status) {  
      		$method_data = array( 
        		'code'         	=> 'memorycoin',
        		'title'      	=> $this->language->get('text_title'),
				'sort_order' 	=> $this->config->get('memorycoin_sort_order'),
      		);
    	}
   
    	return $method_data;
  	}
}
?>
