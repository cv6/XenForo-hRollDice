<?php

/**
 * Helper for choosing a usergroup.
 *
 * @package Hoffi
 */
class Hoffi_Option_UserGroupChooser extends XenForo_Option_UserGroupChooser
{

	/**
	 * Renders the user group chooser option as a group of <input type="checkbox" />.
	 *
	 * @param XenForo_View $view View object
	 * @param string $fieldPrefix Prefix for the HTML form field name
	 * @param array $preparedOption Prepared option info
	 * @param boolean $canEdit True if an "edit" link should appear
	 *
	 * @return XenForo_Template_Abstract Template object
	 */
	public static function renderCheckbox(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		return self::_renderWithoutSpecified('option_list_option_checkbox', $view, $fieldPrefix, $preparedOption, $canEdit);
	}
	
	protected static function _renderWithoutSpecified($templateName, XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$preparedOption['formatParams'] = self::getUserGroupOptions( $preparedOption['option_value'] );

		return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
			$templateName, $view, $fieldPrefix, $preparedOption, $canEdit
		);

	}

}