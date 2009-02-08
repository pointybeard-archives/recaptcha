<?php	
		
	Final Class datasourcereCAPTCHA Extends DataSource{			

		function about(){
			return array(
					 'name' => 'reCAPTCHA: Public Key',
					 'author' => array(
							'name' => 'Symphony Team',
							'website' => 'http://symphony21.com',
							'email' => 'team@symphony21.com'),
					 'version' => '1.0',
					 'release-date' => '2008-04-29');	
		}

		
		public function grab(){
			include_once(EXTENSIONS . '/recaptcha/extension.driver.php');
			$driver = $this->_Parent->ExtensionManager->create('recaptcha');
			return new XMLElement('recaptcha', $driver->getPublicKey());
		}		
		
	}


