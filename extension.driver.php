<?php

	Class extension_recaptcha extends Extension{

		public function about(){
			return array('name' => 'reCAPTCHA',
						 'version' => '1.0',
						 'release-date' => '2008-05-07',
						 'author' => array(	'name' => 'Symphony Team',
											'website' => 'http://symphony21.com',
											'email' => 'team@symphony21.com'),
						 'description' => 'This is an event that uses the reCAPTCHA service to help prevent spam.'
				 		);
		}
		
		public function getSubscribedDelegates(){
			return array(
						array(
							'page' => '/blueprints/events/new/',
							'delegate' => 'AppendEventFilter',
							'callback' => 'addFilterToEventEditor'
						),
						
						array(
							'page' => '/blueprints/events/edit/',
							'delegate' => 'AppendEventFilter',
							'callback' => 'addFilterToEventEditor'
						),
						
						array(
							'page' => '/blueprints/events/new/',
							'delegate' => 'AppendEventFilterDocumentation',
							'callback' => 'addFilterDocumentationToEvent'
						),
											
						array(
							'page' => '/blueprints/events/edit/',
							'delegate' => 'AppendEventFilterDocumentation',
							'callback' => 'addFilterDocumentationToEvent'
						),
						
						array(
							'page' => '/system/preferences/',
							'delegate' => 'AddCustomPreferenceFieldsets',
							'callback' => 'appendPreferences'
						),
						
						array(
							'page' => '/frontend/',
							'delegate' => 'EventPreSaveFilter',
							'callback' => 'processEventData'
						),					
			);
		}
		
		public function addFilterToEventEditor($context){
			$context['options'][] = array('recaptcha', @in_array('recaptcha', $context['selected']) ,'reCAPTCHA Verification');		
		}
		
		public function appendPreferences($context){
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', 'reCAPTCHA Verification'));

			$div = new XMLElement('div', NULL, array('class' => 'group'));
			$label = Widget::Label('Public Key');
			$label->appendChild(Widget::Input('settings[recaptcha][public-key]', General::Sanitize($this->_Parent->Configuration->get('public-key', 'recaptcha'))));		
			$div->appendChild($label);

			$label = Widget::Label('Private Key');
			$label->appendChild(Widget::Input('settings[recaptcha][private-key]', General::Sanitize($this->_Parent->Configuration->get('private-key', 'recaptcha'))));		
			$div->appendChild($label);
			
			$group->appendChild($div);
			
			$group->appendChild(new XMLElement('p', 'Get a reCAPTCHA API public/private key pair from the <a href="http://recaptcha.net/whyrecaptcha.html">reCAPTCHA site</a>.', array('class' => 'help')));
			
			$context['wrapper']->appendChild($group);
						
		}
		
		public function addFilterDocumentationToEvent($context){
			if(!in_array('recaptcha', $context['selected'])) return;
			
			$context['documentation'][] = new XMLElement('h3', 'reCAPTCHA Verification');
			
			$context['documentation'][] = new XMLElement('p', 'Each entry will be passed to the <a href="http://recaptcha.net/whyrecaptcha.html">reCAPTCHA filtering service</a> before saving. Should the challenge words not match, Symphony will terminate execution of the Event, thus preventing the entry from being saved. You will receive notification in the Event XML. <strong>Note: Be sure to set your reCAPTCHA public and private API keys in the <a href="'.URL.'/symphony/system/preferences/">Symphony Preferences</a>.</strong>');
			
			$context['documentation'][] = new XMLElement('p', 'The following is an example of the XML returned form this filter:');
			$code = '<filter type="recaptcha" status="passed" />
<filter type="recaptcha" status="failed">Challenge words entered were invalid.</filter>';

			$context['documentation'][] = contentBlueprintsEvents::processDocumentationCode($code);

		}
		
		public function processEventData($context){

			if(!in_array('recaptcha', $context['event']->eParamFILTERS)) return;
			
			
			include_once(EXTENSIONS . '/recaptcha/lib/recaptchalib.php');
			$resp = recaptcha_check_answer($this->getPrivateKey(),
			                                $_SERVER['REMOTE_ADDR'],
			                                $_POST['recaptcha_challenge_field'],
			                                $_POST['recaptcha_response_field']);

			$context['messages'][] = array('recaptcha', $resp->is_valid, (!$resp->is_valid ? 'Challenge words entered were invalid.' : NULL));

		}
		
		public function uninstall(){
			//ConfigurationAccessor::remove('recaptcha');	
			$this->_Parent->Configuration->remove('recaptcha');
			$this->_Parent->saveConfig();
		}

		public function getPublicKey(){
			//return ConfigurationAccessor::get('public-key', 'recaptcha');
			return $this->_Parent->Configuration->get('public-key', 'recaptcha');
		}	
		
		public function getPrivateKey(){
			//return ConfigurationAccessor::get('private-key', 'recaptcha');
			return $this->_Parent->Configuration->get('private-key', 'recaptcha');
		}			
		
	}

?>