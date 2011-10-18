# Field: XML Select Box

## Installation

1. Download and upload the 'xml_selectbox' folder to your Symphony 'extensions' folder.
2. Enable the extension by selecting "Field: XML Select Box" in the list and choose Enable from the with-selected menu, then click Apply.
3. You can now select the "XML Select Box" field when creating a section.

## Usage

This field is as generic as possible so that it can support many uses. For example:

* any list of items that are too long for the Static Items value of a normal Select Box field (e.g. countries/regions)
* a list of days of the week, or months of the year
* a list of your Flickr sets or Picassa albums for associating with Symphony entries
* availability dates from your Google Calendar
* a list of your Delicious tags so you can maintain a shared taxonomy between your bookmarks and your blog

### Choosing an XML source

XML sources can be loaded into the field in three ways using the "XML Location" option:

1. Using the commonly-used XML files that come with the field. Simply enter the name of the XML file, e.g. `countries_en.xml`
2. Referencing a local XML file in your Symphony site. Prefix the XML path with a forward-slash to locate a file inside your web root e.g. `/workspace/xml/foo.xml` or `/assets/bar.xml`
3. Use the URL of a valid XML source e.g. `http://nick-dunn.co.uk/rss/`. This string can contain params `{$root}` and `{$workspace}` for local URLs.

### Selecting data from the XML

The "Item" option accepts an XPath expression. Each selected node is used to create a new `<option>` in the selectbox. The value attribute (optional) and text value of the `<option>` are evaluated as XPath in the same way, using the "Item" as the context node.

By means of example, you would use this configuration for a list of countries (XML file included with this extension):

XML Location: `countries_en.xml`  
Item: `//country`  
Value: `@abbr`  
Label: `text()`

### Caching

Caching of the XML file is applied only when using a URL as the "XML Location". Local XML files are not cached. If your feed changes infrequently, set the cache interval high to perhaps several hours, days or even weeks. If the data changes frequently then use a lower value (1 minute minimum) but be aware of the performance implications for your users.

The XML is parsed both to build the selectbox in the backend *and* when writing its value to Data Source XML, so a high cache interval is recommended.