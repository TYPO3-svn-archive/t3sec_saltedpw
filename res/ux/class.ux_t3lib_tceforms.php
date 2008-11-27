<?php
/***************************************************************
*  Copyright notice
*
*  (c) Marcus Krause (marcus#exp2008@t3sec.info)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Modifying the t3lib_TCEforms class file so the md5 salted JS gets included
 *
 * $Id$
 *
 * @author	Marcus Krause <marcus#exp2008@t3sec.info>
 */


class ux_t3lib_TCEforms extends t3lib_TCEforms	{

	/**
	 * JavaScript code used for input-field evaluation.
	 *
	 * 		Example use:
	 *
	 * 		$msg.='Distribution time (hh:mm dd-mm-yy):<br /><input type="text" name="send_mail_datetime_hr" onchange="typo3form.fieldGet(\'send_mail_datetime\', \'datetime\', \'\', 0,0);"'.$GLOBALS['TBE_TEMPLATE']->formWidth(20).' /><input type="hidden" value="'.time().'" name="send_mail_datetime" /><br />';
	 * 		$this->extJSCODE.='typo3form.fieldSet("send_mail_datetime", "datetime", "", 0,0);';
	 *
	 * 		... and then include the result of this function after the form
	 *
	 * @param	string		$formname: The identification of the form on the page.
	 * @param	boolean		$update: Just extend/update existing settings, e.g. for AJAX call
	 * @return	string		A section with JavaScript - if $update is false, embedded in <script></script>
	 */
	function JSbottom($formname='forms[0]', $update = false)	{
		$jsFile = array();
		$elements = array();

			// required:
		foreach ($this->requiredFields as $itemImgName => $itemName) {
			$match = array();
			if (preg_match('/^(.+)\[((\w|\d|_)+)\]$/', $itemName, $match)) {
				$record = $match[1];
				$field = $match[2];
				$elements[$record][$field]['required'] = 1;
				$elements[$record][$field]['requiredImg'] = $itemImgName;
				if (isset($this->requiredAdditional[$itemName]) && is_array($this->requiredAdditional[$itemName])) {
					$elements[$record][$field]['additional'] = $this->requiredAdditional[$itemName];
				}
			}
		}
			// range:
		foreach ($this->requiredElements as $itemName => $range) {
			if (preg_match('/^(.+)\[((\w|\d|_)+)\]$/', $itemName, $match)) {
				$record = $match[1];
				$field = $match[2];
				$elements[$record][$field]['range'] = array($range[0], $range[1]);
				$elements[$record][$field]['rangeImg'] = $range['imgName'];
			}
		}

		$this->TBE_EDITOR_fieldChanged_func='TBE_EDITOR.fieldChanged_fName(fName,formObj[fName+"_list"]);';

		if (!$update) {
			if ($this->loadMD5_JS) {
				$this->loadJavascriptLib('md5.js');
				$this->loadJavascriptLib( t3lib_extMgm::extRelPath('t3sec_femd5salted') . 'res/js/md5_salted.js');
			}

			$this->loadJavascriptLib('contrib/prototype/prototype.js');
			$this->loadJavascriptLib('../t3lib/jsfunc.evalfield.js');
			// @TODO: Change to loadJavascriptLib(), but fix "TS = new typoScript()" issue first - see bug #9494
			$jsFile[] = '<script type="text/javascript" src="'.$this->backPath.'jsfunc.tbe_editor.js"></script>';

				// if IRRE fields were processed, add the JavaScript functions:
			if ($this->inline->inlineCount) {
				$this->loadJavascriptLib('contrib/scriptaculous/scriptaculous.js');
				$this->loadJavascriptLib('../t3lib/jsfunc.inline.js');
				$out .= '
				inline.setPrependFormFieldNames("'.$this->inline->prependNaming.'");
				inline.setNoTitleString("'.addslashes(t3lib_BEfunc::getNoRecordTitle(true)).'");
				';
			}

				// Toggle icons:
			$toggleIcon_open = '<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/pil2down.gif','width="12" height="7"').' hspace="2" alt="Open" title="Open" />';
			$toggleIcon_close = '<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/pil2right.gif','width="7" height="12"').' hspace="2" alt="Close" title="Close" />';

			$out .= '
			function getOuterHTML(idTagPrefix)	{	// Function getting the outerHTML of an element with id
				var str=($(idTagPrefix).inspect()+$(idTagPrefix).innerHTML+"</"+$(idTagPrefix).tagName.toLowerCase()+">");
				return str;
			}
			function flexFormToggle(id)	{	// Toggling flexform elements on/off:
				Element.toggle(""+id+"-content");

				if (Element.visible(id+"-content")) {
					$(id+"-toggle").update(\''.$toggleIcon_open.'\')
					$(id+"-toggleClosed").value = 0;
				} else {
					$(id+"-toggle").update(\''.$toggleIcon_close.'\');
					$(id+"-toggleClosed").value = 1;
				}

					var previewContent = "";
					var children = $(id+"-content").getElementsByTagName("input");
					for (var i = 0, length = children.length; i < length; i++) {
					if (children[i].type=="text" && children[i].value)	previewContent+= (previewContent?" / ":"")+children[i].value;
					}
				if (previewContent.length>80)	{
					previewContent = previewContent.substring(0,67)+"...";
				}
				$(id+"-preview").update(previewContent);
			}
			function flexFormToggleSubs(id)	{	// Toggling sub flexform elements on/off:
					var descendants = $(id).immediateDescendants();
				var isOpen=0;
				var isClosed=0;
					// Traverse and find how many are open or closed:
					for (var i = 0, length = descendants.length; i < length; i++) {
					if (descendants[i].id)	{
						if (Element.visible(descendants[i].id+"-content"))	{isOpen++;} else {isClosed++;}
					}
					}

					// Traverse and toggle
					for (var i = 0, length = descendants.length; i < length; i++) {
					if (descendants[i].id)	{
						if (isOpen!=0 && isClosed!=0)	{
							if (Element.visible(descendants[i].id+"-content"))	{flexFormToggle(descendants[i].id);}
						} else {
							flexFormToggle(descendants[i].id);
						}
					}
					}
			}
			function flexFormSortable(id)	{	// Create sortables for flexform sections
				Sortable.create(id, {tag:\'div\',constraint: false, onChange:function(){
					setActionStatus(id);
				} });
			}
			function setActionStatus(id)	{	// Updates the "action"-status for a section. This is used to move and delete elements.
					var descendants = $(id).immediateDescendants();

					// Traverse and find how many are open or closed:
					for (var i = 0, length = descendants.length; i < length; i++) {
					if (descendants[i].id)	{
						$(descendants[i].id+"-action").value = descendants[i].visible() ? i : "DELETE";
					}
					}
			}

			TBE_EDITOR.images.req.src = "'.t3lib_iconWorks::skinImg($this->backPath,'gfx/required_h.gif','',1).'";
			TBE_EDITOR.images.cm.src = "'.t3lib_iconWorks::skinImg($this->backPath,'gfx/content_client.gif','',1).'";
			TBE_EDITOR.images.sel.src = "'.t3lib_iconWorks::skinImg($this->backPath,'gfx/content_selected.gif','',1).'";
			TBE_EDITOR.images.clear.src = "'.$this->backPath.'clear.gif";

			TBE_EDITOR.auth_timeout_field = '.intval($GLOBALS['BE_USER']->auth_timeout_field).';
			TBE_EDITOR.formname = "'.$formname.'";
			TBE_EDITOR.formnameUENC = "'.rawurlencode($formname).'";
			TBE_EDITOR.backPath = "'.addslashes($this->backPath).'";
			TBE_EDITOR.prependFormFieldNames = "'.$this->prependFormFieldNames.'";
			TBE_EDITOR.prependFormFieldNamesUENC = "'.rawurlencode($this->prependFormFieldNames).'";
			TBE_EDITOR.prependFormFieldNamesCnt = '.substr_count($this->prependFormFieldNames,'[').';
			TBE_EDITOR.isPalettedoc = '.($this->isPalettedoc ? addslashes($this->isPalettedoc) : 'null').';
			TBE_EDITOR.doSaveFieldName = "'.($this->doSaveFieldName ? addslashes($this->doSaveFieldName) : '').'";
			TBE_EDITOR.labels.fieldsChanged = '.$GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.fieldsChanged')).';
			TBE_EDITOR.labels.fieldsMissing = '.$GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.fieldsMissing')).';
			TBE_EDITOR.labels.refresh_login = '.$GLOBALS['LANG']->JScharCode($this->getLL('m_refresh_login')).';
			TBE_EDITOR.labels.onChangeAlert = '.$GLOBALS['LANG']->JScharCode($this->getLL('m_onChangeAlert')).';
			evalFunc.USmode = '.($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat']?'1':'0').';
			';
		}

			// add JS required for inline fields
		if (count($this->inline->inlineData)) {
			$out .=	'
			inline.addToDataArray('.t3lib_div::array2json($this->inline->inlineData).');
			';
		}
			// Registered nested elements for tabs or inline levels:
		if (count($this->requiredNested)) {
			$out .= '
			TBE_EDITOR.addNested('.t3lib_div::array2json($this->requiredNested).');
			';
		}
			// elements which are required or have a range definition:
		if (count($elements)) {
			$out .= '
			TBE_EDITOR.addElements('.t3lib_div::array2json($elements).');
			TBE_EDITOR.initRequired();
			';
		}
			// $this->additionalJS_submit:
		if ($this->additionalJS_submit) {
			$additionalJS_submit = implode('', $this->additionalJS_submit);
			$additionalJS_submit = str_replace("\r", '', $additionalJS_submit);
			$additionalJS_submit = str_replace("\n", '', $additionalJS_submit);
			$out .= '
			TBE_EDITOR.addActionChecks("submit", "'.addslashes($additionalJS_submit).'");
			';
		}

		$out .= chr(10).implode(chr(10),$this->additionalJS_post).chr(10).$this->extJSCODE;
		$out .= '
			TBE_EDITOR.loginRefreshed();
		';

			// Regular direct output:
		if (!$update) {
			$spacer = chr(10) . chr(9);
			$out  = $spacer . implode($spacer, $jsFile) . t3lib_div::wrapJS($out);
		}

		return $out;
	}
}
?>