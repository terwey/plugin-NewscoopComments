<?php
/**
 * @package Facebook Meta Plugin
 * @author Yorick Terweijden <yorick.terweijden@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Newscoop facebook_meta block plugin
 *
 * Type:     block
 * Name:     facebook_meta
 * Purpose:  Generates the Facebook Meta information for a page
 *
 * @param string
 *     $params
 * @param string
 *     $p_smarty
 * @param string
 *     $content
 *
 * @return
 *
 */
function smarty_block_comment2_form($params, $content, &$smarty, &$repeat)
{

    $html = '';
    $smarty->smarty->loadPlugin('smarty_shared_escape_special_chars');
    $context = $smarty->getTemplateVars('gimme');
    $formService = \Zend_Registry::get('container')->getService('newscoop_comments.form.service.class');

    // var_dump($formService);
    $form = $formService->createForm();
    
    var_dump($form);

    if ($context->article->defined) {
        if (isset($content)) {
            $html .= '<h1>comment block test</h1> <h2>content is set</h2>';
            $html .= '<pre>'.$content.'</pre>';
            $html .= $form;
        } else {
            $html .= '<h1>comment block test</h1> <h2>content is not set</h2>';
        }
    }

    return $html;
}