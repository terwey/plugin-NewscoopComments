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

    var_dump($params);

    // smarty processes the $content after the second call
    // this means that the first time we have to configure and set the formStart
    if ($repeat) {
        if ($params['type'] == 'comment') {
            $formService = \Zend_Registry::get('container')->getService('newscoop_comments.form.service');
            $formService->config($params['fields']);
            
            if (!$formService instanceof Newscoop\CommentsBundle\Services\FormService) {
                return 'Comment bundle is not active';
            }
        }
        $html .= $formService->getFormStart();
    }

    // the second time we have to call the container again (do not config!) and get the required parameters
    if ($context->article->defined) {
        if (!$repeat) {
            if (isset($content)) {
                $html .= $content;
            }
            $formService = \Zend_Registry::get('container')->getService('newscoop_comments.form.service');
            $html .= $formService->getFormEnd();
        }
    }

    return $html;
}