<?php

class Cloudflare_DDNS_v4 {
	
	public $username = '';
	public $password = '';
	public $zonename = '';
	
	private $zoneid = '';
	
	private function getCurlHeaders() {
		$headers = array();
		
		$headers[] = sprintf('X-Auth-Email: %s', $this->username);
		$headers[] = sprintf('X-Auth-Key: %s', $this->password);
		$headers[] = 'Content-Type: application/json';
		
		return $headers;
	}
	
	private function init() {
		if ($this->zoneid == '') {
			$rp = $this->getZones($this->zonename);
			
			if ($rp['success'] == 1) {
				
				if (count($rp['result']) > 0) {
					$this->zoneid = $rp['result'][0]['id'];
				} else {
					exit('Zone not exists:' . $this->zonename);
				}
			} else {
				exit($rp);
			}
		}
	}
	
	private function getZones($name) {
		$url = 'https://api.cloudflare.com/client/v4/zones?';
		$uri = $url . sprintf('name=%s', $name);
		
		$headers = $this->getCurlHeaders();
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$rp = curl_exec($ch);
		curl_close($ch);
		
		$result = json_decode($rp, TRUE);
		
		return $result;
	}
	
	public function __construct($zonename, $username, $password) {
		$this->zonename = $zonename;
		$this->username = $username;
		$this->password = $password;
	}
	

	
	
	public function getDNSRecords($type='A') {
		$this->init();
		
		$url = 'https://api.cloudflare.com/client/v4/zones/' . $this->zoneid . '/dns_records?';
		$uri = $url . sprintf('type=%s', $type);
		
		$headers = $this->getCurlHeaders();
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$rp = curl_exec($ch);
		curl_close($ch);
		
		$result = json_decode($rp, TRUE);
		
		return $result;
	}
	
	public function addDNSRecord($name, $type='A', $content='127.0.0.1') {
		$this->init();
		
		$records = $this->getDNSRecords();
		
		if ($records['success'] == 1) {
			$record_name = $name . '.' . $this->zonename;
			
			foreach($records['result'] as $record) {
				if ($record['name'] == $record_name) {
					exit('Record name exists: ' . $record_name);
				}
			}
		}
		
		$url = 'https://api.cloudflare.com/client/v4/zones/' . $this->zoneid . '/dns_records';
		$uri = $url . sprintf('type=%s', $type);
		
		$headers = $this->getCurlHeaders();
		
		$data = array(
			'type' => $type,
			'name' => $name . '.' . $this->zonename,
			'content' => $content,
			'ttl' => 120,
			'priority' => 10,
			'proxied' => false
		);
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		
		$rp = curl_exec($ch);
		curl_close($ch);
		
		$result = json_decode($rp, TRUE);
		
		return $result;
	}
	
	public function UpdateDNSRecord($name, $content, $type='A') {
		$this->init();
		
		$url = 'https://api.cloudflare.com/client/v4/zones/' . $this->zoneid . '/dns_records/';
		
		$records = $this->getDNSRecords();
		
		if ($records['success'] == 1) {
			$record_name = $name . '.' . $this->zonename;
			
			foreach($records['result'] as $record) {
				if ($record['name'] == $record_name) {
					
					$uri = $url . $record['id'];
					
					$headers = $this->getCurlHeaders();
					
					$data = array(
						'type' => $type,
						'name' => $name . '.' . $this->zonename,
						'content' => $content,
						'ttl' => 120,
						'priority' => 10,
						'proxied' => false
					);
					
					$ch = curl_init();
		
					curl_setopt($ch, CURLOPT_URL, $uri);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
					
					$rp = curl_exec($ch);
					curl_close($ch);
					
					$result = json_decode($rp, TRUE);
					
					return $result;
				}
			}
		} else {
			exit('Record name not exists:' . $name);
		}
	}
	
}
