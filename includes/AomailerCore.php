<?php 
class AomailerCore
{	
	public $order_id;
	public $settings;
	public function __construct()
	{
		$config_path = realpath(AOMP_AOMAILER_DIR) . DIRECTORY_SEPARATOR . 'config.php';
		if (file_exists($config_path)) {
			$this->settings = require($config_path);
		}
		$this->settings = $this->settings + AomailerDB::aomp()->loadSettings('core');
	}
	/**
	 * listner()
	 */
	public function listner()
	{
		add_action('woocommerce_email_order_details', [$this, 'order_details'], 10, 4);
		add_action('woocommerce_checkout_update_order_meta', [$this, 'new_order'], 20, 1);
		add_action('woocommerce_order_status_completed', [$this, 'payment_order'], 10, 1);
        //add_action('woocommerce_checkout_update_user_meta', [$this, 'new_user'], 12, 2);
        add_action('woocommerce_order_status_changed', [$this, 'new_status'], 11, 3);
	}
	/**
	 * order_details($orde=object, $admin=boolean, $text=string, $email)
	 */
	public function order_details($order, $admin, $text, $email)
	{
		$this->order_id = $order->get_id();		
		add_action('phpmailer_init', [$this, 'aomp_send'], 10, 1);
	}
	/**
	 * new_order($order_id)
	 */
	public function new_order($order_id)
	{
		$order = wc_get_order($order_id);		
		$order_data = $order->get_data();
		$status = $order_data['status'];
		if (!empty($this->settings['admin_event_used'])) {
			if (empty($this->settings['admin_event_new_order'])) {
				return false;
			}	
			$data = self::prepareData('admin', 1, $order_data, true);
			if (empty($data)) {
				return false;
			}
			$send = AomailerSMSApi::aomp()->send($data);	
		}
		if (!empty($this->settings['client_event_used'])) {
			if (empty($this->settings['client_event_new_order'])) {
				return false;
			}	
			$data = self::prepareData('client', 1, $order_data, true);
			if (empty($data)) {
				return false;
			}
			$send = AomailerSMSApi::aomp()->send($data);
		}
		return false;
	}
	/**
	 * payment_order($order_id)
	 */
	public function payment_order($order_id)
	{
		$order = wc_get_order($order_id);
				$order_data = $order->get_data();		
		$status = $order_data['status'];
		if (!empty($this->settings['admin_event_used'])) {
			if (empty($this->settings['admin_event_payment_order'])) {
				return false;
			}	
			$data = self::prepareData('admin', 2, $order_data);
			if (empty($data)) {
				return false;
			}
			$send = AomailerSMSApi::aomp()->send($data);	
		}
		if (!empty($this->settings['client_event_used'])) {
			if (empty($this->settings['client_event_payment_order'])) {
				return false;
			}
			$data = self::prepareData('client', 2, $order_data);
			if (empty($data)) {
				return false;
			}
			$send = AomailerSMSApi::aomp()->send($data);	
		}
		return false;
	}
	/**
	 * new_user($user_id, $fields)
	 */
	public function new_user($user_id, $fields)
	{
		return false;
	}
	/**
	 * new_status($order_id, $old_status, $new_status)
	 */
	public function new_status($order_id, $old_status, $new_status)
	{
		$order = wc_get_order($order_id);
		$order_data = $order->get_data();
		if (!empty($this->settings['admin_event_used'])) {
			if (!empty($this->settings['admin_event_change_order_3']) && $new_status=='pending') {
				$data = self::prepareData('admin', 3, $order_data);
				$send = AomailerSMSApi::aomp()->send($data);
			}
			if (!empty($this->settings['admin_event_change_order_4']) && $new_status=='processing') {
				$data = self::prepareData('admin', 4, $order_data);
				$send = AomailerSMSApi::aomp()->send($data);
			}
			if (!empty($this->settings['admin_event_change_order_5']) && $new_status=='on-hold') {
				$data = self::prepareData('admin', 5, $order_data);
				$send = AomailerSMSApi::aomp()->send($data);
			}
			if (!empty($this->settings['admin_event_change_order_6']) && $new_status=='cancelled') {
				$data = self::prepareData('admin', 6, $order_data);
				$send = AomailerSMSApi::aomp()->send($data);
			}
			if (!empty($this->settings['admin_event_change_order_7']) && $new_status=='refunded') {
				$data = self::prepareData('admin', 7, $order_data);
				$send = AomailerSMSApi::aomp()->send($data);
			}
			if (!empty($this->settings['admin_event_change_order_8']) && $new_status=='failed') {
				$data = self::prepareData('admin', 8, $order_data);
				$send = AomailerSMSApi::aomp()->send($data);
			}
			if (!empty($this->settings['used_custom_events'])) {
				if (!empty($this->settings['custom_status'])) {
					$event = "admin_event_" . $new_status;
					if  (!empty($this->settings[$event])) {
						$id = $this->settings['custom_status_id'][$event];
						$data = self::prepareData('admin', $id, $order_data);
						$send = AomailerSMSApi::aomp()->send($data);
					}
				}		
			}
		}
		if (!empty($this->settings['client_event_used'])) {
			if (!empty($this->settings['client_event_change_order_3']) && $new_status=='pending') {
				$data = self::prepareData('client', 3, $order_data);
				$send = AomailerSMSApi::aomp()->send($data);
			}
			if (!empty($this->settings['client_event_change_order_4']) && $new_status=='processing') {
				$data = self::prepareData('client', 4, $order_data);
				$send = AomailerSMSApi::aomp()->send($data);
			}
			if (!empty($this->settings['client_event_change_order_5']) && $new_status=='on-hold') {
				$data = self::prepareData('client', 5, $order_data);
				$send = AomailerSMSApi::aomp()->send($data);
			}
			if (!empty($this->settings['client_event_change_order_6']) && $new_status=='cancelled') {
				$data = self::prepareData('client', 6, $order_data);
				$send = AomailerSMSApi::aomp()->send($data);
			}
			if (!empty($this->settings['client_event_change_order_7']) && $new_status=='refunded') {
				$data = self::prepareData('client', 7, $order_data);
				$send = AomailerSMSApi::aomp()->send($data);
			}
			if (!empty($this->settings['client_event_change_order_8']) && $new_status=='failed') {
				$data = self::prepareData('client', 8, $order_data);
				$send = AomailerSMSApi::aomp()->send($data);
			}
			if (!empty($this->settings['used_custom_events'])) {
				if (!empty($this->settings['custom_status'])) {
					$event = "client_event_" . $new_status;
					if  (!empty($this->settings[$event])) {
						$id = $this->settings['custom_status_id'][$event];
						$data = self::prepareData('client', $id, $order_data);
						$send = AomailerSMSApi::aomp()->send($data);
					}
				}		
			}
		}
		return false;
	}
	/**
	 * aomp_send($mailer)
	 */
	public function aomp_send($mailer)
	{
		$db_data = AomailerDB::aomp()->select_all();
		if (!empty($db_data)) {
			foreach ($db_data as $obj) {
				if ($obj->param_name=='admin_number') {
					$this->settings[$obj->param_name] = @unserialize($obj->param_value);
				} else {
					$this->settings[$obj->param_name] = $obj->param_value;
				}
			}
		}
		if (empty($this->settings['admin_email_used'])) {
			return $mailer;
		}
		$order = wc_get_order($this->order_id);
		$order_data = $order->get_data();
		$mailer_data = self::parseMailer($mailer);
		$status = $order_data['status'];
		$first_name = $order_data['billing']['first_name'];
		$last_name = $order_data['billing']['last_name'];
		$security = [
			'login' => $this->settings['login'],
			'passwd' => $this->settings['passwd'],
		];
		/* by default we use order[id] */
		$id_order = $this->order_id;
		/* Sequential Order Number uses order[number] instead of order[id] */	
		if (is_plugin_active('wt-woocommerce-sequential-order-numbers/wt-advanced-order-number.php')){
			if (!empty($this->settings['used_sequential_order_number'])){
				$id_order = !empty($order_data['number']) ? $order_data['number'] : $this->order_id;
			}
		}
		$data = [
			'id_order' => $id_order,
			'id_user' => $order->get_user_id(),
			'id_system' => 1,
			'identifier' => $_SERVER['HTTP_HOST'],        
			'id_service' => '',        				    
			'id_client' => '',                            
			'id_mailer' => '', 
			'letter' => [
				'subject' => $mailer->Subject,
				'body' => $mailer->Body,
				'sender' => $mailer->Sender,
				'from' => !empty($this->settings['from']) ? $this->settings['from'] : $mailer->From,
				'fromname' => !empty($this->settings['email_from_name']) ? $this->settings['email_from_name'] : $mailer->FromName,
				'replyto' => !empty($this->settings['reply_to']) ? $this->settings['reply_to'] : $mailer_data['reply_to'],
				'replytoname' => !empty($this->settings['reply_to_name']) ? $this->settings['reply_to_name'] : $mailer_data['reply_to_name'],
				'to' => $mailer_data['to'],
			],
		];
		if (AomailerEmailApi::aomp()->send($data, $security)) {
			$mailer->ClearAllRecipients();
		}
		return $mailer;
	}
	/**
	 * parseMailer($mailer=[])
	 */
	private function parseMailer($mailer=[])
	{
		$data = [];		
		$reflected = new ReflectionObject($mailer);
		$property  = $reflected->getProperty('to');
		$property->setAccessible(true);
		$to = $property->getValue($mailer);
		if (is_array($to)) {
			foreach ($to as $value) {
				if (is_array($value)) {
					if (!filter_var($value[0], FILTER_VALIDATE_EMAIL)) {
						continue;
					}
					$data['to'] = $value[0];
				} elseif (!empty($value)) {
					if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
						continue;
					}	
					$data['to'] = $value;
					break;
				}	
			}
		} else {	
			if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
				$data['to'] = $to;
			}
		}
		foreach ((array) $mailer as $k=>$v) {
			if (preg_match('/(ReplyTo)/', $k)) {
				foreach ($v as $value) {
					if (is_array($value)) {
						$data['reply_to'] = $value[0];
						$data['reply_to_name'] = $value[1];
					} else {
						$data['reply_to'] = $value;
					}
					break;
				}
			}
		}
		return $data;
	}
	/**
	 * prepareData($type=0, $id=0, $array=[])
	 */
	private function prepareData($type=0, $id=0, $array=[], $sequential_fix = false)
	{
		if (empty($type) || empty($id)) {
			return false;
		}
		$data = [
			'login' => $this->settings['login'],
			'passwd' => $this->settings['passwd'],
		];
		$data['message'][0]['name_delivery'] = 'wordpress_changeorderstatus';
		if (!is_array($this->settings[$type.'_template_settings']) && is_string($this->settings[$type.'_template_settings'])) {
			$this->settings[$type.'_template_settings'] = @unserialize($this->settings[$type.'_template_settings']);
		}
		if (!empty($this->settings[$type.'_template_settings'][$id][$type.'_from_name'])) {
			$data['message'][0]['from_name'] = $this->settings[$type.'_template_settings'][$id][$type.'_from_name'];
		} else {
			return false;
		}
		if (!empty(trim($this->settings[$type.'_template_settings'][$id][$type.'_text_sms']))) {
			$stores_name = get_bloginfo();
			$data['StoresName'] = !empty($stores_name) ? $stores_name : '';
			/*by default we use order[id]*/
			$data['OrderID'] = !empty($array['id']) ? $array['id'] : '';
			/* Sequential Order Number uses order[number] instead of order[id] */			
			if (is_plugin_active('wt-woocommerce-sequential-order-numbers/wt-advanced-order-number.php')){
				if (!empty($this->settings['used_sequential_order_number'])){
					if (!empty($data['OrderID']) && $sequential_fix == true) {
						$data['OrderID'] = self::fix_for_sequential_order_number($data['OrderID']);
					} else {
						$data['OrderID'] = !empty($array['number']) ? $array['number'] : '';
					}
				}
			}
			$data['OrderSum'] = (!empty($array['total']) && !empty($array['currency'])) ? $array['total'].' '.$array['currency'] : '';
			$data['ClientName'] = !empty($array['billing']['first_name']) ? $array['billing']['first_name'] : '';
			$data['ClientLastName'] = !empty($array['billing']['last_name']) ? $array['billing']['last_name'] : '';
			if (!empty($array['status'])) {
				if (array_key_exists($array['status'], $this->settings['custom_status'])) {
					$data['OrderStatus'] = $this->settings['custom_status'][$array['status']];
				} else {
					$data['OrderStatus'] = __($this->settings['status'][$array['status']], 'aomailer');
				}				
			} else {
				$data['OrderStatus'] = '';
			}
			$data['AddrOrderDelivery'] = '';
			if (!empty($array['shipping']['city'])) {
				$data['AddrOrderDelivery'] .= $array['shipping']['city'].' ';
			}
			if (!empty($array['shipping']['address_2'])) {
				$data['AddrOrderDelivery'] .= $array['shipping']['address_2'].' ';
			}
			if (!empty($array['shipping']['address_1'])) {
				$data['AddrOrderDelivery'] .= $array['shipping']['address_1'].' ';
			}
			$data['AddrPayment'] = '';
			if (!empty($array['billing']['city'])) {
				$data['AddrPayment'] .= $array['billing']['city'].' ';
			}
			if (!empty($array['billing']['address_2'])) {
				$data['AddrPayment'] .= $array['billing']['address_2'].' ';
			}
			if (!empty($array['billing']['address_1'])) {
				$data['AddrPayment'] .= $array['billing']['address_1'].' ';
			}
			if (!empty($array['billing']['email'])) {
				$data['ClientEmail'] = $array['billing']['email'];
			}
			if (!empty($array['billing']['phone'])) {
				$data['ClientPhone'] = $array['billing']['phone'];
			}
			$data['MethodPayment'] = !empty($array['payment_method_title']) ? $array['payment_method_title'] : '';
			$data['message'][0]['sms_text'] = AomailerSMSApi::aomp()->replaceTag($this->settings[$type.'_template_settings'][$id][$type.'_text_sms'], $data, $this->settings['tag']);
			if (!empty($this->settings[$type.'_template_settings'][$id][$type.'_used_translit'])) {
				$data['message'][0]['sms_text'] = AomailerSMSApi::aomp()->transliterate($data['message'][0]['sms_text']);
			}
		} else {
			return false;
		}
		if ($type=='admin') {
			if (empty($this->settings[$type.'_template_settings'][$id][$type.'_number'])) {
				return false;
			}
			// admin_numbers will be boolean(false) if string can't be unserialized
			$admin_numbers = @unserialize($this->settings[$type.'_template_settings'][$id][$type.'_number']);
			if (is_array($admin_numbers)) {
				$this->settings[$type.'_template_settings'][$id][$type.'_number'] = $admin_numbers;
			}
			if (!is_array($this->settings[$type.'_template_settings'][$id][$type.'_number'])) {
				return false;
			}
			foreach ($this->settings[$type.'_template_settings'][$id][$type.'_number'] as $number) {
				$data['message'][0]['abonents'][] = [
					'number' => trim($number),
					'time_send' => '',
					'validity_period' => '',
				];
			}
		}
		if ($type=='client') {
			if (!empty($array['billing']['phone'])) {
				$data['message'][0]['abonents'][] = [
					'number' =>  AomailerSMSApi::aomp()->getFormat($array['billing']['phone'], 'phone'),
					'time_send' => '',
					'validity_period' => '',
				];
			} else {
				return false;
			}
		}
		return $data;	
	}
	public function fix_for_sequential_order_number($id)
	{
		global $wpdb;
		$sql = "SELECT `meta_value` FROM `".$wpdb->prefix ."postmeta` WHERE `post_id` = $id AND `meta_key`='_order_number'";
		$result = $wpdb->get_results($sql, 'ARRAY_A'); 	
		if (!empty($result)){
			return $result[0]['meta_value'];
		} else {
			return $id;
		}
	}	
	/**
	 * aomp($className=__CLASS__)
	 */ 
	public static function aomp($className=__CLASS__)
	{
		return new $className;
	}
}
