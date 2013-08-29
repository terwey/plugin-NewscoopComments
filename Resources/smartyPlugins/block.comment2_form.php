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

    $request = \Zend_Registry::get('container')->getService('request');
    // var_dump($request);
    // smarty processes the $content after the second call
    // this means that the first time we have to configure and set the formStart
    if ($repeat) {
        if ($params['type'] == 'comment') {
            $formService = \Zend_Registry::get('container')->getService('newscoop_comments.form.service');
            if ($request->getMethod() == 'POST') {
                // var_dump($formService->handleRequest($request));
                $formService->config($params['fields'], $request);
                if (!$formService->isValid()) {
                    var_dump($formService->getErrors());
                }
            } else {
                $formService->config($params['fields'], $request);
            }
            
            if (!$formService instanceof Newscoop\CommentsBundle\Services\FormService) {
                return 'Comment bundle is not active';
            }
        }
        print $formService->getFormStart(); // print and not return
    }

    // the second time we have to call the container again (do not config!) and get the required parameters
    if ($context->article->defined) {
        if (!$repeat) {
            $formService = \Zend_Registry::get('container')->getService('newscoop_comments.form.service');
            if ($formService->isValid()) {
                return 'This form has been posted. Whut up yo?!';
            } else {
                if (isset($content)) {
                    $html .= $content;
                }
                $html .= $formService->getFormEnd();
            }
        }
    }

    return $html;
}