<?php

/*
	Question2Answer (c) Gideon Greenspan

	http://www.question2answer.org/


	File: qa-include/qa-page-message.php
	Version: See define()s at top of qa-include/qa-base.php
	Description: Controller for private messaging page


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../');
		exit;
	}

	require_once QA_INCLUDE_DIR.'qa-db-selects.php';
	require_once QA_INCLUDE_DIR.'qa-app-users.php';
	require_once QA_INCLUDE_DIR.'qa-app-format.php';
	require_once QA_INCLUDE_DIR.'qa-app-limits.php';

	$loginUserId = qa_get_logged_in_userid();


//	Check which box we're showing (inbox/sent), we're not using Q2A's single-sign on integration and that we're logged in

	$req = qa_request_part(1);
	if ($req === null)
		$showBox = 'inbox';
	else if ($req === 'sent')
		$showBox = 'outbox';
	else
		return include QA_INCLUDE_DIR.'qa-page-not-found.php';

	if (QA_FINAL_EXTERNAL_USERS)
		qa_fatal_error('User accounts are handled by external code');

	if (!isset($loginUserId)) {
		$qa_content = qa_content_prepare();
		$qa_content['error'] = qa_insert_login_links(qa_lang_html('misc/message_must_login'), qa_request());
		return $qa_content;
	}

	if (!qa_opt('allow_private_messages') || !qa_opt('show_message_history'))
		return include QA_INCLUDE_DIR.'qa-page-not-found.php';


//	Find the user profile and questions and answers for this handle

	$func = 'qa_db_messages_'.$showBox.'_selectspec';
	$pmSpec = $func('private', $loginUserId, true);
	$userMessages = qa_db_select_with_pending($pmSpec);


//	Prepare content for theme

	$qa_content = qa_content_prepare();
	$qa_content['title'] = qa_lang_html('misc/pm_'.$showBox.'_title');

	$qa_content['message_list'] = array(
		'tags' => 'id="privatemessages"',
		'messages' => array(),
	);

	$htmlDefaults = qa_message_html_defaults();
	if ($showBox === 'outbox')
		$htmlDefaults['towhomview'] = true;

	foreach ($userMessages as $message)
		$qa_content['message_list']['messages'][] = qa_message_html_fields($message, $htmlDefaults);

	$qa_content['navigation']['sub'] = qa_messages_sub_navigation($showBox);

	return $qa_content;
