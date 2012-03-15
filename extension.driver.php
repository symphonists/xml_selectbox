<?php

	Class extension_xml_selectbox extends Extension{
		
		public function uninstall(){
			Symphony::Database()->query("DROP TABLE `tbl_fields_xml_selectbox`");
		}
		
		public function install(){
			return Symphony::Database()->query("CREATE TABLE `tbl_fields_xml_selectbox` (
			  `id` int(11) NOT NULL auto_increment,
			  `field_id` int(11) NOT NULL,
			  `allow_multiple_selection` enum('yes','no') NOT NULL default 'no',
			  `xml_location` varchar(255) NOT NULL default '',
			  `item_xpath` varchar(255) NOT NULL default '',
			  `text_xpath` varchar(255) NOT NULL,
			  `value_xpath` varchar(255) default NULL,
			  `cache` int(11) NOT NULL default 0,
			  PRIMARY KEY (`id`)
			) TYPE=MyISAM");
		}
		
	}