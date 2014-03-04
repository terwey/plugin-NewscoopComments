<?php

/**
 * form_builder_element
 * Smarty Plugin for creating a Form Element
 * 
 * @example <pre>
 * {{ form_builder_element element="commentparent" options=["value"=>"41"] }}
 * {{ form_builder_element element="name" options=['label'=>'name'] }}
 * {{ form_builder_element element="email" options=['label'=>'email'] }}
 * {{ form_builder_element element="subject" options=['label'=>'subject'] }}
 * {{ form_builder_element element="content" options=['label'=>'content'] }}
 * </pre>
 *
 * @param array $params
 * @param Smarty $smarty
 *
 * @return string HTML form element
 */
function smarty_function_form_builder_element($params = array(), &$smarty)
{
	$context = $smarty->getTemplateVars('gimme');
	if (!is_null($context->article->number)) {
	    $formService = \Zend_Registry::get('container')->getService('newscoop_comments.form.service');
	    $allowedElements = array('content', 'subject', 'save', 'name', 'email', 'url', 'spam_protect', 'recaptcha', 'captcha', 'commentparent');

	    $element = (in_array($params['element'], $allowedElements)) ? $params['element'] : null;
	    $optionParameters = (!empty($params['options'])) ? $params['options'] : null;

	    return (!empty($element)) ? $formService->getElement($element, $optionParameters) : '';
	} else {
		return '';
	}
}