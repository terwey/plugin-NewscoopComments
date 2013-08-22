<?php

function smarty_function_comment2_form_element($p_params = array(), &$p_smarty)
{
    $formService = \Zend_Registry::get('container')->getService('newscoop_comments.form.service');
    $allowedElements = array('content', 'subject', 'save', 'name', 'email', 'url', 'spam_protect');

    $element = (in_array($p_params['element'], $allowedElements)) ? $p_params['element'] : null;
    $optionParameters = (!empty($p_params['options'])) ? $p_params['options'] : null;
    // var_dump($formService);

    return (!empty($element)) ? $formService->getElement($element, $optionParameters) : '';
}