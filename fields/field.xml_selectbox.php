<?php
	
	require_once(TOOLKIT . '/class.gateway.php');
	require_once(CORE . '/class.cacheable.php');
	
	Class fieldXML_Selectbox extends Field {
		function __construct(&$parent){
			parent::__construct($parent);
			$this->_name = __('XML Select Box');
			$this->set('show_column', 'no');		
		}
		
		function allowDatasourceParamOutput(){
			return true;
		}
		
		function canFilter(){
			return true;
		}

		function isSortable(){
			return true;
		}
		
		public function appendFormattedElement(&$wrapper, $data, $encode = false) {
			if (!is_array($data) or empty($data)) return;
			
			$list = new XMLElement($this->get('element_name'));
			
			$labels = $this->getSelectedLabels($data);
			
			foreach ($labels as $value => $label) {
				$list->appendChild(new XMLElement(
					'item', General::sanitize($label), array(
					'value'	=> $value
					)
				));
			}

			$wrapper->appendChild($list);
		}
		
		function getValuesFromXML() {
			
			$xml_location = $this->get('xml_location');
			$cache_life = (int) $this->get('cache');
			
			require_once(TOOLKIT . '/util.validators.php');
						
			if (preg_match($validators['URI'], $xml_location)) {
				// is a URL, check cache
								
				$cache_id = md5('xml_selectbox_' . $xml_location);
				$cache = new Cacheable($this->_Parent->_Parent->Database);
				$cachedData = $cache->check($cache_id);
			
			
				if(!$cachedData) {
					
						$ch = new Gateway;
						$ch->init();
						$ch->setopt('URL', $xml_location);
						$ch->setopt('TIMEOUT', 6);
						$xml = $ch->exec();
						$writeToCache = true;
						
						$cache->write($cache_id, $xml, $cache_life); // Cache life is in minutes not seconds e.g. 2 = 2 minutes

						$xml = trim($xml);
						if (empty($xml) && $cachedData) $xml = $cachedData['data'];
					
				} else {					
					$xml = $cachedData['data'];
				}
				
				$xml = simplexml_load_string($xml);
				
			} elseif (substr($xml_location, 0, 1) == '/') {
				// relative to DOCROOT
				$xml = simplexml_load_file(DOCROOT . $this->get('xml_location'));
			} else {
				// in extension's /xml folder
				$xml = simplexml_load_file(EXTENSIONS . '/xml_selectbox/xml/' . $this->get('xml_location'));
			}
			
			if (!$xml) return;
			
			$items = $xml->xpath($this->get('item_xpath'));
			
			$options = array();
			
			foreach($items as $item) {
				
				$option = array();
				
				$text_xpath = $item->xpath($this->get('text_xpath'));
				$option['text'] = General::sanitize((string)$text_xpath[0]);
				
				if ($this->get('value_xpath') != '') {
					$value_xpath = $item->xpath($this->get('value_xpath'));
					$option['value'] = General::sanitize((string)$value_xpath[0]);
				}
				
				if ((string)$option['value'] == '') $option['value'] = $option['text'];
				
				$options[] = $option;
				
			}
			
			return $options;			
		}
		
		function getSelectedLabels($data = null) {
			$states = $this->getValuesFromXML();
			$selected = array();
			
			if(!is_array($data['value'])) $data['value'] = array($data['value']);
			
			foreach($states as $state){
				if (in_array($state['value'], $data['value'])) $selected[$state['value']] = $state['text'];
			}
			return $selected;
		}

		function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL){
			header('content-type: text/plain');
			$states = $this->getValuesFromXML();
			
			if(!is_array($data['value'])) $data['value'] = array($data['value']);
			
			$options = array();
			
			$value_found = false;
			foreach($states as $state){
				$selected = in_array($state['value'], $data['value']);
				if ($selected == true) $value_found = true;
				$options[] = array($state['value'], $selected, $state['text']);
			}
			
			if ($value_found == false && $data[0] != null) {
				$options[] = array($data['value'][0], $data['value'][0]);
			}
			
			$fieldname = 'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix;
			if($this->get('allow_multiple_selection') == 'yes') $fieldname .= '[]';
			
			$label = Widget::Label($this->get('label'));
			$label->appendChild(Widget::Select($fieldname, $options, ($this->get('allow_multiple_selection') == 'yes' ? array('multiple' => 'multiple') : NULL)));
			
			if($flagWithError != NULL) $wrapper->appendChild(Widget::wrapFormElementWithError($label, $flagWithError));
			else $wrapper->appendChild($label);		
		}

		function prepareTableValue($data, XMLElement $link=NULL){
			$value = $data['value'];
			
			if(!is_array($value)) $value = array($value);
			
			$labels = $this->getSelectedLabels($data);
			
			return parent::prepareTableValue(array('value' => @implode(', ', $labels)), $link);
			
		}
		
		public function getParameterPoolValue($data){
			return $data;
		}

		public function processRawFieldData($data, &$status, $simulate=false, $entry_id=NULL){

			$status = self::__OK__;

			if(!is_array($data)) return array('value' => General::sanitize($data));

			if(empty($data)) return NULL;
			
			$result = array('value' => array());

			foreach($data as $value){ 
				$result['value'][] = $value;
			}		
			
			return $result;
		}
		
		function commit(){
			
			if(!parent::commit()) return false;
			
			$id = $this->get('id');

			if($id === false) return false;
			
			$fields = array();
			
			$fields['field_id'] = $id;
			if($this->get('xml_location') != '') $fields['xml_location'] = $this->get('xml_location');			
			if($this->get('item_xpath') != '') $fields['item_xpath'] = $this->get('item_xpath');
			if($this->get('text_xpath') != '') $fields['text_xpath'] = $this->get('text_xpath');
			if($this->get('value_xpath') != '') $fields['value_xpath'] = $this->get('value_xpath');
			if($this->get('cache') != '') $fields['cache'] = $this->get('cache');
			$fields['allow_multiple_selection'] = ($this->get('allow_multiple_selection') ? $this->get('allow_multiple_selection') : 'no');

			$this->Database->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '$id' LIMIT 1");
			
			if(!$this->Database->insert($fields, 'tbl_fields_' . $this->handle())) return false;
			
			return true;
					
		}
		
		function findDefaults(&$fields){
			if(!isset($fields['allow_multiple_selection'])) $fields['allow_multiple_selection'] = 'no';
		}
				
		public function displaySettingsPanel(&$wrapper, $errors = null) {
			parent::displaySettingsPanel($wrapper, $errors);
			
			$div = new XMLElement('div', NULL, array('class' => 'group'));
			
			$label = Widget::Label(__('XML Location'));
			$input = Widget::Input('fields['.$this->get('sortorder').'][xml_location]', General::sanitize($this->get('xml_location')));
			$label->appendChild($input);
			$div->appendChild($label);
			
			$label = Widget::Label(__('Item (XPath)'));
			$input = Widget::Input('fields['.$this->get('sortorder').'][item_xpath]', General::sanitize($this->get('item_xpath')));
			$label->appendChild($input);
			$div->appendChild($label);
			
			$wrapper->appendChild($div);
			
			$div = new XMLElement('div', NULL, array('class' => 'group'));
			
			$label = Widget::Label(__('Value (XPath)'));
			$label->appendChild(new XMLElement('i', __('Optional')));
			$input = Widget::Input('fields['.$this->get('sortorder').'][value_xpath]', General::sanitize($this->get('value_xpath')));
			$label->appendChild($input);
			$div->appendChild($label);
			
			$label = Widget::Label(__('Label (XPath)'));
			$input = Widget::Input('fields['.$this->get('sortorder').'][text_xpath]', General::sanitize($this->get('text_xpath')));
			$label->appendChild($input);
			$div->appendChild($label);
			
			$wrapper->appendChild($div);
			
			## Cached time input
			$div = new XMLElement('div', NULL, array('class' => 'group'));
			
			$label = Widget::Label();
			$input = Widget::Input('fields['.$this->get('sortorder').'][cache]', max(1, intval($this->get('cache'))), NULL, array('size' => '6'));
			$label->setValue('Update cached result every ' . $input->generate() . ' minutes');
			if(isset($this->_errors[$this->get('sortorder')]['cache'])) $div->appendChild(Widget::wrapFormElementWithError($label, $this->_errors[$this->get('sortorder')]['cache']));
			else $div->appendChild($label);
			
			$wrapper->appendChild($div);
			
			
			## Allow selection of multiple items
			$label = Widget::Label();
			$input = Widget::Input('fields['.$this->get('sortorder').'][allow_multiple_selection]', 'yes', 'checkbox');
			if($this->get('allow_multiple_selection') == 'yes') $input->setAttribute('checked', 'checked');			
			$label->setValue(__('%s Allow selection of multiple options', array($input->generate())));
			$wrapper->appendChild($label);
			
			$this->appendShowColumnCheckbox($wrapper);
						
		}
		
		function createTable(){
			return $this->_engine->Database->query(
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `entry_id` int(11) unsigned NOT NULL,
				  `value` varchar(255) default NULL,
				  PRIMARY KEY  (`id`),
				  KEY `entry_id` (`entry_id`),
				  KEY `value` (`value`)
				) TYPE=MyISAM;"
			
			);
		}

	}