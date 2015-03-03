<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Visol.' . $_EXTKEY,
	'Managepanels',
	array(
		'Panel' => 'startup, dashboard, managePanels, startPanel, new, create, edit, update, delete, duplicate, editVotings',
		'Voting' => 'listForCurrentUser, edit, update, delete, new, duplicate, sort',
		'VotingOption' => 'listForVoting, new,edit,update,delete,sort'

	),
	// non-cacheable actions
	array(
		'Panel' => 'managePanels, startPanel, new, create, edit, update, delete, duplicate, editVotings',
		'Voting' => 'listForCurrentUser, edit, update, delete, new, duplicate, sort',
		'VotingOption' => 'listForVoting, new,edit,update,delete,sort',
	)
);
