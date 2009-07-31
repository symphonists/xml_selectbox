<?php

	Class extension_xml_selectbox extends Extension{
	
		public function about(){
			return array('name' => 'Field: XML Select Box',
						 'version' => '0.1',
						 'release-date' => '2009-07-22',
						 'author' => array('name' => 'Nick Dunn',
										   'website' => 'http://nick-dunn.co.uk',
										   'email' => 'nick@nick-dunn.co.uk')
				 		);
		}
		
		public function uninstall(){
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_xml_selectbox`");
		}

		public function install(){
			return $this->_Parent->Database->query("CREATE TABLE `tbl_fields_xml_selectbox` (
			  `id` int(11) NOT NULL auto_increment,
			  `field_id` int(11) NOT NULL,
			  `allow_multiple_selection` enum('yes','no') NOT NULL default 'no',
			  `xml_location` varchar(255) NOT NULL default '',
			  `item_xpath` varchar(255) NOT NULL default '',
			  `text_xpath` varchar(255) NOT NULL,
			  `value_xpath` varchar(255) default NULL,
			  PRIMARY KEY (`id`)
			) TYPE=MyISAM");
		}
			
	}

