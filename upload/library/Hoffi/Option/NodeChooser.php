<?php

class Hoffi_Option_NodeChooser {

    public static function renderSingle(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        $editLink = $view->createTemplateObject('option_list_option_editlink', array(
            'preparedOption' => $preparedOption,
            'canEditOptionDefinition' => $canEdit
        ));

        $nodeModel = XenForo_Model::create('XenForo_Model_Node');

        $forumOptions = $nodeModel->getNodeOptionsArray($nodeModel->getAllNodes(), $preparedOption['option_value'], '(unspecified)');

        return $view->createTemplateObject('option_list_option_select', array(
            'fieldPrefix' => $fieldPrefix,
            'listedFieldName' => $fieldPrefix . '_listed[]',
            'preparedOption' => $preparedOption,
            'formatParams' => $forumOptions,
            'editLink' => $editLink
        ));
    }


    public static function renderMultiple(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {

        $editLink = $view->createTemplateObject('option_list_option_editlink', array(
            'preparedOption' => $preparedOption,
            'canEditOptionDefinition' => $canEdit
        ));

        $nodeModel = XenForo_Model::create('XenForo_Model_Node');

        $forumOptions = $nodeModel->getNodeOptionsArray($nodeModel->getAllNodes(), $preparedOption['option_value'], '(unspecified)');

        return $view->createTemplateObject('simpleportal_nodeoptions', array(
            'fieldPrefix' => $fieldPrefix,
            'listedFieldName' => $fieldPrefix . '_listed[]',
            'preparedOption' => $preparedOption,
            'formatParams' => $forumOptions,
            'editLink' => $editLink
        ));
    }
}