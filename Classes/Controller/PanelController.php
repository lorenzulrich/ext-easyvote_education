<?php
namespace Visol\EasyvoteEducation\Controller;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Lorenz Ulrich <lorenz.ulrich@visol.ch>, visol digitale Dienstleistungen GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Visol\Easyvote\Utility\Algorithms;
use Visol\EasyvoteEducation\Domain\Model\Panel;

/**
 * PanelController
 */
class PanelController extends \Visol\EasyvoteEducation\Controller\AbstractController {

	/**
	 * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication frontendUserAuthentication
	*/
	protected $frontendUserAuthentication;

	public function __construct() {
		parent::__construct();
		$this->frontendUserAuthentication = $GLOBALS['TSFE']->fe_user;
	}

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		$panels = $this->panelRepository->findAll();
		$this->view->assign('panels', $panels);
	}

	/**
	 * action show
	 *
	 * @param Panel $panel
	 * @return void
	 */
	public function showAction(Panel $panel) {
		$this->view->assign('panel', $panel);
	}

	/**
	 * action new
	 *
	 * @param Panel $newPanel
	 * @ignorevalidation $newPanel
	 * @return void
	 */
	public function newAction(Panel $newPanel = NULL) {
		if ($this->getLoggedInUser()) {
			$this->view->assign('newPanel', $newPanel);
		} else {
			// todo access denied
		}
	}

	/**
	 * Correct parsing of datetime-local input
	 */
	protected function initializeCreateAction(){
		$propertyMappingConfiguration = $this->arguments['newPanel']->getPropertyMappingConfiguration();
		$propertyMappingConfiguration->forProperty('date')->setTypeConverterOption('TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\DateTimeConverter', \TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT, 'Y-m-d\TH:i');
		if (!empty($this->arguments['newPanel']->getValue('fromTime'))) {
			$propertyMappingConfiguration->forProperty('fromTime')->setTypeConverter($this->objectManager->get('Visol\\Easyvote\\Property\\TypeConverter\\TimestampConverter'))->setTypeConverterOption('Visol\\Easyvote\\Property\\TypeConverter\\TimestampConverter', \Visol\Easyvote\Property\TypeConverter\TimestampConverter::CONFIGURATION_DATE_FORMAT, 'H:i');
		}
		if (!empty($this->arguments['newPanel']->getValue('toTime'))) {
			$propertyMappingConfiguration->forProperty('toTime')->setTypeConverter($this->objectManager->get('Visol\\Easyvote\\Property\\TypeConverter\\TimestampConverter'))->setTypeConverterOption('Visol\\Easyvote\\Property\\TypeConverter\\TimestampConverter', \Visol\Easyvote\Property\TypeConverter\TimestampConverter::CONFIGURATION_DATE_FORMAT, 'H:i');
		}
	}

	/**
	 * action create
	 *
	 * @param Panel $newPanel
	 * @return string
	 */
	public function createAction(Panel $newPanel) {
		if ($communityUser = $this->getLoggedInUser()) {
			do {
				$panelId = Algorithms::generateRandomString(4, 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789');
			} while ($this->panelRepository->findOneByPanelId($panelId) instanceof Panel);
			$newPanel->setPanelId($panelId);
			$newPanel->setCommunityUser($communityUser);
			$this->panelRepository->add($newPanel);
			$this->persistenceManager->persistAll();
			$message = LocalizationUtility::translate('panel.actions.create.success', $this->request->getControllerExtensionName(), array($newPanel->getTitle()));
			$this->addFlashMessage($message, '', AbstractMessage::OK);
			return json_encode(array(
				'redirectToAction' => 'managePanels'
			));
		} else {
			// TODO no user logged in
		}
	}

	/**
	 * action edit
	 *
	 * @param Panel $panel
	 * @ignorevalidation $panel
	 * @return string
	 */
	public function editAction(Panel $panel) {
		if ($this->isCurrentUserOwnerOfPanel($panel)) {
			$this->view->assign('panel', $panel);
			return json_encode(array('content' => $this->view->render()));
		} else {
			// todo permission denied
		}
	}

	protected function initializeUpdateAction() {
		$propertyMappingConfiguration = $this->arguments['panel']->getPropertyMappingConfiguration();
		$propertyMappingConfiguration->forProperty('date')->setTypeConverterOption('TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\DateTimeConverter', \TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter::CONFIGURATION_DATE_FORMAT, 'd.m.y');
		$propertyMappingConfiguration->forProperty('fromTime')->setTypeConverter($this->objectManager->get('Visol\\Easyvote\\Property\\TypeConverter\\TimestampConverter'))->setTypeConverterOption('Visol\\Easyvote\\Property\\TypeConverter\\TimestampConverter', \Visol\Easyvote\Property\TypeConverter\TimestampConverter::CONFIGURATION_DATE_FORMAT, 'H:i');
		$propertyMappingConfiguration->forProperty('toTime')->setTypeConverter($this->objectManager->get('Visol\\Easyvote\\Property\\TypeConverter\\TimestampConverter'))->setTypeConverterOption('Visol\\Easyvote\\Property\\TypeConverter\\TimestampConverter', \Visol\Easyvote\Property\TypeConverter\TimestampConverter::CONFIGURATION_DATE_FORMAT, 'H:i');
	}

	/**
	 * action update
	 *
	 * @param Panel $panel
	 * @return string
	 */
	public function updateAction(Panel $panel) {
		if ($this->isCurrentUserOwnerOfPanel($panel)) {
			$message = LocalizationUtility::translate('panel.actions.update.success', $this->request->getControllerExtensionName(), array($panel->getTitle()));
			$this->addFlashMessage($message, '', AbstractMessage::OK);
			$this->panelRepository->update($panel);
			$this->persistenceManager->persistAll();
			return json_encode(array(
				'redirectToAction' => 'managePanels'
			));
		} else {
			// todo permission denied
		}
	}

	/**
	 * action delete
	 *
	 * @param Panel $panel
	 * @return string
	 */
	public function deleteAction(Panel $panel) {
		if ($this->isCurrentUserOwnerOfPanel($panel)) {
			$message = LocalizationUtility::translate('panel.actions.delete.success', $this->request->getControllerExtensionName(), array($panel->getTitle()));
			$this->addFlashMessage($message, '', AbstractMessage::OK);
			$this->panelRepository->remove($panel);
			$this->persistenceManager->persistAll();
			return json_encode(array(
				'redirectToAction' => 'managePanels'
			));
		} else {
			// todo permission denied
		}
	}

	/**
	 * action duplicate
	 *
	 * @param Panel $panel
	 * @return string
	 */
	public function duplicateAction(Panel $panel) {
		if ($this->isCurrentUserOwnerOfPanel($panel)) {
			$message = LocalizationUtility::translate('panel.actions.duplicate.success', $this->request->getControllerExtensionName(), array($panel->getTitle()));
			$this->addFlashMessage($message, '', AbstractMessage::OK);
			/** @var \Visol\EasyvoteEducation\Domain\Model\Panel $duplicatePanel */
			$duplicatePanel = $this->cloneService->copy($panel);
			// generate a new panelId
			do {
				$panelId = Algorithms::generateRandomString(8, 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789');
			} while ($this->panelRepository->findOneByPanelId($panelId) instanceof Panel);
			$duplicatePanel->setPanelId($panelId);
			// Prefix "Copy of" to duplicated panel
			$copyOfText = LocalizationUtility::translate('panel.actions.duplicate.copyOf', $this->request->getControllerExtensionName());
			$duplicatePanel->setTitle($copyOfText . ' ' . $panel->getTitle());

			$this->panelRepository->add($duplicatePanel);
			$this->persistenceManager->persistAll();
			return json_encode(array(
				'redirectToAction' => 'managePanels'
			));
		} else {
			// todo permission denied
		}

	}

	/**
	 *
	 *
	 * @param Panel $panel
	 * @return string
	 */
	public function editVotingsAction(Panel $panel) {
		if ($this->isCurrentUserOwnerOfPanel($panel)) {
			$this->view->assign('panel', $panel);
			return json_encode(array('content' => $this->view->render()));
		} else {
			// todo permission denied
		}
	}

	/**
	 * @param Panel $panel
	 * @return string
	 */
	public function executeAction(Panel $panel) {
		if ($this->isCurrentUserOwnerOfPanel($panel)) {
			$this->view->assign('panel', $panel);
			$guestViewUri = $this->uriBuilder->setCreateAbsoluteUri(TRUE)->build();
			$this->view->assign('guestViewUri', urlencode($guestViewUri));
			return json_encode(array('content' => $this->view->render()));
		} else {
			// todo permission denied
		}
	}

	/**
	 * @param string $actionarguments The action arguments
	 * @return string
	 */
	public function votingStepAction($actionarguments) {
		$actionArgumentsArray = GeneralUtility::trimExplode('-', $actionarguments);
		$protectedActions = array('startPanel', 'nextVoting', 'startVoting', 'stopVoting', 'stopPanel');
		$publicActions = array('guestViewContent', 'presentationViewContent', 'castVote');

		if (count($actionArgumentsArray === 4)) {
			// we need four parts in the array for the request to be valid
			list($unusedPanelObjectName, $panelUid, $votingStepAction, $votingUid) = $actionArgumentsArray;
			/* @var \Visol\EasyvoteEducation\Domain\Model\Panel $panel */
			$panel = $this->panelRepository->findByUid((int)$panelUid);
			if (in_array($votingStepAction, $protectedActions)) {
				// action can only be performed by the owner of the panel, security check
				if ($this->isCurrentUserOwnerOfPanel($panel)) {
					// the owner is making the request, so it is valid
					switch ($votingStepAction) {
						case 'startPanel':
							$panel->setCurrentState('');
							break;

						case 'nextVoting':
							$panel->setCurrentState('pendingVoting-' . $votingUid);
							break;

						case 'startVoting':
							// set voting to enabled
							$panel->getCurrentVoting()->setIsVotingEnabled(TRUE);
							$this->votingRepository->update($panel->getCurrentVoting());

							$panel->setCurrentState('currentVoting-' . $votingUid);
							break;

						case 'stopVoting':
							// set voting to disabled
							$panel->getCurrentVoting()->setIsVotingEnabled(FALSE);
							$this->votingRepository->update($panel->getCurrentVoting());
							$this->votingService->processVotingResult($panel);
							$panel->setCurrentState('finishedVoting-' . $votingUid);
							break;

						case 'stopPanel':
							$panel->setCurrentState('finishedPanel-0');
							break;
					}

					$this->panelRepository->update($panel);
					$this->persistenceManager->persistAll();

					$this->view->assign('votingStepAction', $votingStepAction);
					$this->view->assign('panel', $panel);
					return $this->view->render();
				} else {
					// todo permission denied
				}
			} elseif (in_array($votingStepAction, $publicActions)) {
				// action can be performed anonymously, proceed
				if ($votingStepAction === 'castVote') {
					// for this function, $votingUid contains the uid of the chosen votingOption
					$votingOption = $this->votingOptionRepository->findByUid((int)$votingUid);
					if ($votingOption instanceof \Visol\EasyvoteEducation\Domain\Model\VotingOption) {
						if ($votingOption->getVoting()->getIsVotingEnabled()) {
							$condensedVotingName = 'panel-' . $panel->getUid() . '-castVote' . $votingOption->getUid();
							if ($this->frontendUserAuthentication->getSessionData('easyvoteeducation-castVote') !== $condensedVotingName) {
								/** @var \Visol\EasyvoteEducation\Domain\Model\Vote $newVote */
								$newVote = $this->objectManager->get('Visol\EasyvoteEducation\Domain\Model\Vote');
								$this->voteRepository->add($newVote);
								$votingOption->addVote($newVote);
								$this->votingOptionRepository->update($votingOption);
								$this->persistenceManager->persistAll();
								// save information about cast vote to session to prevent double-casting
								$this->frontendUserAuthentication->setAndSaveSessionData('easyvoteeducation-castVote', $condensedVotingName);
							} else {
								// TODO do nothing - vote was cast before for user
							}
						}
					}
				} else {
					$this->view->assign('originalVotingStepAction', ucfirst($votingStepAction));
					$votingStepAction = $this->votingService->getViewNameForCurrentPanelState($panel, $votingStepAction);
				}
				$this->view->assign('votingStepAction', $votingStepAction);
				$this->view->assign('panel', $panel);
				return $this->view->render();
			} else {
				// todo action not allowed
			}
		} else {
			// todo invalid request
		}
	}

	/**
	 * action guestViewLogin
	 */
	public function guestViewLoginAction() {
	}

	/**
	 * Check if panel is available
	 *
	 * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
	 */
	public function initializeGuestViewParticipationAction() {
		if ($this->request->hasArgument('panelId')) {
			// check if there is a panel with this ID
			$panelId = $this->request->getArgument('panelId');
			/** @var \Visol\EasyvoteEducation\Domain\Model\Panel $panel */
			$panel = $this->panelRepository->findOneByPanelId($panelId);
			if (!$panel instanceof \Visol\EasyvoteEducation\Domain\Model\Panel) {
				$message = LocalizationUtility::translate('panel.guestView.panelNotFound', $this->request->getControllerExtensionName());
				$this->flashMessageContainer->add($message, '', AbstractMessage::ERROR);
				$this->redirect('guestViewLogin');
			}
		}
	}

	/**
	 * action guestViewParticipation
	 * @param string $panelId
	 */
	public function guestViewParticipationAction($panelId) {
		/** @var \Visol\EasyvoteEducation\Domain\Model\Panel $panel */
		$panel = $this->panelRepository->findOneByPanelId($panelId);
		$this->view->assign('panel', $panel);
	}

	/**
	 * action presentationView
	 */
	public function presentationViewLoginAction() {

	}

	/**
	 * Check if panel is available
	 *
	 * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
	 */
	public function initializePresentationViewParticipationAction() {
		if ($this->request->hasArgument('panelId')) {
			// check if there is a panel with this ID
			$panelId = $this->request->getArgument('panelId');
			/** @var \Visol\EasyvoteEducation\Domain\Model\Panel $panel */
			$panel = $this->panelRepository->findOneByPanelId($panelId);
			if (!$panel instanceof \Visol\EasyvoteEducation\Domain\Model\Panel) {
				$message = LocalizationUtility::translate('panel.guestView.panelNotFound', $this->request->getControllerExtensionName());
				$this->flashMessageContainer->add($message, '', AbstractMessage::ERROR);
				$this->redirect('guestViewLogin');
			}
		}
	}

	/**
	 * action guestViewParticipation
	 * @param string $panelId
	 */
	public function presentationViewParticipationAction($panelId) {
		/** @var \Visol\EasyvoteEducation\Domain\Model\Panel $panel */
		$panel = $this->panelRepository->findOneByPanelId($panelId);
		$this->view->assign('panel', $panel);
	}


	/**
	 * action startup
	 */
	public function startupAction() {
	}

	/**
	 * action dashboard
	 */
	public function dashboardAction() {
	}

	/**
	 * action managePanels
	 *
	 * @return void
	 */
	public function managePanelsAction() {
		if ($communityUser = $this->getLoggedInUser()) {
			$this->view->assign('panels', $this->panelRepository->findByCommunityUser($communityUser));
		} else {
			// todo no user logged in
		}
	}

	/**
	 * action startPanel
	 *
	 * @return void
	 */
	public function startPanelAction() {
		if ($communityUser = $this->getLoggedInUser()) {
			$this->view->assign('panels', $communityUser->getPanels());
		} else {
			// todo no user logged in
		}
	}

}