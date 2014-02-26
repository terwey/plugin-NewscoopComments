<?php

/**
 * comment2_form_element
 * Smarty Plugin for creating a Form Element
 * 
 * @example <pre>
 * {{ comment2_form_element element="commentparent" options=["value"=>"41"] }}
 * {{ comment2_form_element element="name" options=['label'=>'name'] }}
 * {{ comment2_form_element element="email" options=['label'=>'email'] }}
 * {{ comment2_form_element element="subject" options=['label'=>'subject'] }}
 * {{ comment2_form_element element="content" options=['label'=>'content'] }}
 * </pre>
 *
 * @param array $p_params
 * @param Smarty $p_smarty
 *
 * @return string HTML form element
 */
function smarty_function_comment2_form_element($p_params = array(), &$p_smarty)
{
    $formService = \Zend_Registry::get('container')->getService('newscoop_comments.form.service');
    $allowedElements = array('content', 'subject', 'save', 'name', 'email', 'url', 'spam_protect', 'recaptcha', 'captcha', 'commentparent');

    $element = (in_array($p_params['element'], $allowedElements)) ? $p_params['element'] : null;
    $optionParameters = (!empty($p_params['options'])) ? $p_params['options'] : null;
    // var_dump($formService);

    return (!empty($element)) ? $formService->getElement($element, $optionParameters) : '';
}