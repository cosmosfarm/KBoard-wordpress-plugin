<?php
/**
 * KBoard 아임포트 연동
 * @link www.cosmosfarm.com
 * @copyright Copyright 2020 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
final class KBIamport {
	
	var $imp_id;
	var $imp_key;
	var $imp_secret;
	
	private $access_token;
	private $expired_at;
	
	private $payments_url = 'https://api.iamport.kr/payments';
	private $cancel_url = 'https://api.iamport.kr/payments/cancel';
	private $accesstoken_url = 'https://api.iamport.kr/users/getToken';
	
	public function payments($imp_uid){
		$payment = new stdClass();
		$payment->success = false;
		$payment->message = '';
		$payment->data = new stdClass();
		
		if($imp_uid && $this->getAccessToken()){
			
			$args = array();
			$args['method'] = 'GET';
			$args['headers'] = array('Authorization' => $this->getAccessToken());
			
			$response = wp_remote_request(sprintf('%s/%s', $this->payments_url, $imp_uid), $args);
			
			$data = json_decode($response['body']);
			if($response['response']['code'] != '200'){
				$payment->message = $data->message;
			}
			else if(!$data->response){
				$payment->success = false;
				$payment->message = $data->message;
			}
			else{
				$payment->success = true;
				$payment->message = $data->message;
				$payment->data = $data->response;
				if(isset($payment->data->custom_data)) $payment->data->custom_data = json_decode($payment->data->custom_data);
			}
		}
		
		return $payment;
	}
	
	public function cancel($imp_uid, $body=array()){
		$payment = new stdClass();
		$payment->success = false;
		$payment->message = '';
		$payment->data = new stdClass();
		
		if($imp_uid && $this->getAccessToken()){
			
			$args = array();
			$args['method'] = 'POST';
			$args['headers'] = array('Authorization' => $this->getAccessToken());
			$args['body'] = $body;
			$args['body']['imp_uid'] = $imp_uid;
			
			$response = wp_remote_request(sprintf('%s', $this->cancel_url), $args);
			
			$data = json_decode($response['body']);
			if($response['response']['code'] != '200'){
				$payment->message = $data->message;
			}
			else if(!$data->response){
				$payment->success = false;
				$payment->message = $data->message;
			}
			else{
				$payment->success = true;
				$payment->message = $data->message;
				$payment->data = $data->response;
				if(isset($payment->data->custom_data)) $payment->data->custom_data = json_decode($payment->data->custom_data);
			}
		}
		
		return $payment;
	}
	
	public function getAccessToken(){
		if(current_time('timestamp') < $this->expired_at && $this->access_token){
			return $this->access_token;
		}
		
		$args = array();
		$args['method'] = 'POST';
		$args['body'] = array(
			'imp_key'    => $this->imp_key,
			'imp_secret' => $this->imp_secret
		);
		
		$response = wp_remote_request(sprintf('%s', $this->accesstoken_url), $args);
		
		if($response['response']['code'] != '200'){
			echo $response['response']['message'];
			$this->access_token = '';
		}
		else{
			$data = json_decode($response['body']);
			if($data->response){
				$this->expired_at = current_time('timestamp') + ($data->response->expired_at - $data->response->now);
				$this->access_token = $data->response->access_token;
			}
		}
		
		return $this->access_token;
	}
}
?>