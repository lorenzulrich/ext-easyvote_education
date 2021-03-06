<?php
namespace Visol\EasyvoteEducation\Domain\Model;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Panel
 */
class Panel extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * @var \Visol\EasyvoteEducation\Service\VotingService
	 * @inject
	 */
	protected $votingService;

	/**
	 * @var \Visol\EasyvoteEducation\Service\PanelService
	 * @inject
	 */
	protected $panelService;

	/**
	 * Panel identifier
	 *
	 * @var string
	 * @copy ignore
	 */
	protected $panelId = '';

	/**
	 * Title
	 *
	 * @var string
	 * @validate NotEmpty
	 * @copy clone
	 */
	protected $title = '';

	/**
	 * Description
	 *
	 * @var string
	 * @copy clone
	 */
	protected $description = '';

	/**
	 * Date
	 *
	 * @var \DateTime
	 * @copy clone
	 */
	protected $date = NULL;

	/**
	 * From time
	 *
	 * @var integer
	 * @copy clone
	 */
	protected $fromTime = NULL;

	/**
	 * To time
	 *
	 * @var integer
	 * @copy clone
	 */
	protected $toTime = NULL;

	/**
	 * Room
	 *
	 * @var string
	 * @copy clone
	 */
	protected $room = '';

	/**
	 * Address
	 *
	 * @var string
	 * @copy clone
	 */
	protected $address = '';

	/**
	 * Organization
	 *
	 * @var string
	 * @copy clone
	 */
	protected $organization = '';

	/**
	 * Class
	 *
	 * @var string
	 * @copy clone
	 */
	protected $class = '';

	/**
	 * Number of participants (approx.)
	 *
	 * @var string
	 * @copy clone
	 */
	protected $numberOfParticipants = '';

	/**
	 * Current state of panel
	 *
	 * @var string
	 * @copy ignore
	 */
	protected $currentState = '';

	/**
	 * Terms accepted
	 *
	 * @var boolean
	 * @validate NotEmpty
	 * @copy clone
	 */
	protected $termsAccepted = FALSE;

	/**
	 * City
	 *
	 * @var \Visol\Easyvote\Domain\Model\City|NULL
	 * @copy reference
	 */
	protected $city = NULL;

	/**
	 * Image
	 *
	 * @var \Visol\Easyvote\Domain\Model\FileReference
	 * @copy clone
	 */
	protected $image = NULL;

	/**
	 * CommunityUser (owner)
	 *
	 * @var \Visol\Easyvote\Domain\Model\CommunityUser
	 * @copy reference
	 */
	protected $communityUser = NULL;

	/**
	 * Votings
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Visol\EasyvoteEducation\Domain\Model\Voting>
	 * @cascade remove
	 * @copy clone
	 */
	protected $votings = NULL;

	/**
	 * The next voting
	 *
	 * @var \Visol\EasyvoteEducation\Domain\Model\Voting|NULL
	 * @transient
	 */
	protected $nextVoting = NULL;

	/**
	 * The current voting
	 *
	 * @var \Visol\EasyvoteEducation\Domain\Model\Voting|NULL
	 * @transient
	 */
	protected $currentVoting = NULL;

	/**
	 * Panel Invitations
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Visol\EasyvoteEducation\Domain\Model\PanelInvitation>
	 * @cascade remove
	 * @lazy
	 */
	protected $panelInvitations = NULL;

	/**
	 * Were panel invitations sent?
	 *
	 * @var boolean
	 */
	protected $panelInvitationsSent = FALSE;

	/**
	 * Was a feedback e-mail sent?
	 *
	 * @var boolean
	 */
	protected $feedbackMailSent = FALSE;

	/**
	 * Was a "one month before panel" reminder sent?
	 *
	 * @var boolean
	 */
	protected $reminderOnemonthSent = FALSE;

	/**
	 * Was a "two weeks before panel" reminder sent?
	 *
	 * @var boolean
	 */
	protected $reminderTwoweeksSent = FALSE;

	/**
	 * Was a "one week before panel" reminder sent?
	 *
	 * @var boolean
	 */
	protected $reminderOneweekSent = FALSE;

	/**
	 * @var int
	 * @transient
	 */
	protected $numberOfAllowedPanelInvitations = 2;

	/**
	 * @var boolean
	 * @transient
	 */
	protected $panelInvitationAllowed = FALSE;

	/**
	 * @var boolean
	 * @transient
	 */
	protected $allPanelInvitationsAccepted = FALSE;

	/**
	 * __construct
	 */
	public function __construct() {
		//Do not remove the next line: It would break the functionality
		$this->initStorageObjects();
	}

	/**
	 * Initializes all ObjectStorage properties
	 * Do not modify this method!
	 * It will be rewritten on each save in the extension builder
	 * You may modify the constructor of this class instead
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->votings = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->panelInvitations = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

	/**
	 * Returns the title
	 *
	 * @return string $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the title
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Returns the description
	 *
	 * @return string $description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Sets the description
	 *
	 * @param string $description
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * Returns the date
	 *
	 * @return \DateTime $date
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * Sets the date
	 *
	 * @param \DateTime $date
	 * @return void
	 */
	public function setDate(\DateTime $date = NULL) {
		$this->date = $date;
	}

	/**
	 * @return integer
	 */
	public function getFromTime() {
		return $this->fromTime;
	}

	/**
	 * @param integer $fromTime
	 */
	public function setFromTime($fromTime) {
		$this->fromTime = $fromTime;
	}

	/**
	 * @return integer
	 */
	public function getToTime() {
		return $this->toTime;
	}

	/**
	 * @param integer $toTime
	 */
	public function setToTime($toTime) {
		$this->toTime = $toTime;
	}

	/**
	 * Returns the room
	 *
	 * @return string $room
	 */
	public function getRoom() {
		return $this->room;
	}

	/**
	 * Sets the room
	 *
	 * @param string $room
	 * @return void
	 */
	public function setRoom($room) {
		$this->room = $room;
	}

	/**
	 * Returns the address
	 *
	 * @return string $address
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * Sets the address
	 *
	 * @param string $address
	 * @return void
	 */
	public function setAddress($address) {
		$this->address = $address;
	}

	/**
	 * Returns the organization
	 *
	 * @return string $organization
	 */
	public function getOrganization() {
		return $this->organization;
	}

	/**
	 * Sets the organization
	 *
	 * @param string $organization
	 * @return void
	 */
	public function setOrganization($organization) {
		$this->organization = $organization;
	}

	/**
	 * Returns the class
	 *
	 * @return string $class
	 */
	public function getClass() {
		return $this->class;
	}

	/**
	 * Sets the class
	 *
	 * @param string $class
	 * @return void
	 */
	public function setClass($class) {
		$this->class = $class;
	}

	/**
	 * Returns the numberOfParticipants
	 *
	 * @return string $numberOfParticipants
	 */
	public function getNumberOfParticipants() {
		return $this->numberOfParticipants;
	}

	/**
	 * Sets the numberOfParticipants
	 *
	 * @param string $numberOfParticipants
	 * @return void
	 */
	public function setNumberOfParticipants($numberOfParticipants) {
		$this->numberOfParticipants = $numberOfParticipants;
	}

	/**
	 * Returns the termsAccepted
	 *
	 * @return boolean $termsAccepted
	 */
	public function getTermsAccepted() {
		return $this->termsAccepted;
	}

	/**
	 * Sets the termsAccepted
	 *
	 * @param boolean $termsAccepted
	 * @return void
	 */
	public function setTermsAccepted($termsAccepted) {
		$this->termsAccepted = $termsAccepted;
	}

	/**
	 * Returns the boolean state of termsAccepted
	 *
	 * @return boolean
	 */
	public function isTermsAccepted() {
		return $this->termsAccepted;
	}

	/**
	 * Returns the city
	 *
	 * @return \Visol\Easyvote\Domain\Model\City $city
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * Sets the city
	 *
	 * @param \Visol\Easyvote\Domain\Model\City $city
	 * @return void
	 */
	public function setCity(\Visol\Easyvote\Domain\Model\City $city = NULL) {
		$this->city = $city;
	}

	/**
	 * Returns the image
	 *
	 * @return \Visol\Easyvote\Domain\Model\FileReference $image
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * Sets the image
	 *
	 * @param \Visol\Easyvote\Domain\Model\FileReference $image
	 * @return void
	 */
	public function setImage(\Visol\Easyvote\Domain\Model\FileReference $image) {
		$this->image = $image;
	}

	/**
	 * Adds a Voting
	 *
	 * @param \Visol\EasyvoteEducation\Domain\Model\Voting $voting
	 * @return void
	 */
	public function addVoting(\Visol\EasyvoteEducation\Domain\Model\Voting $voting) {
		$this->votings->attach($voting);
	}

	/**
	 * Removes a Voting
	 *
	 * @param \Visol\EasyvoteEducation\Domain\Model\Voting $votingToRemove The Voting to be removed
	 * @return void
	 */
	public function removeVoting(\Visol\EasyvoteEducation\Domain\Model\Voting $votingToRemove) {
		$this->votings->detach($votingToRemove);
	}

	/**
	 * Returns the votings
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Visol\EasyvoteEducation\Domain\Model\Voting> $votings
	 */
	public function getVotings() {
		return $this->votings;
	}

	/**
	 * Sets the votings
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Visol\EasyvoteEducation\Domain\Model\Voting> $votings
	 * @return void
	 */
	public function setVotings(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $votings) {
		$this->votings = $votings;
	}

	/**
	 * @return \Visol\Easyvote\Domain\Model\CommunityUser
	 */
	public function getCommunityUser() {
		return $this->communityUser;
	}

	/**
	 * @param \Visol\Easyvote\Domain\Model\CommunityUser $communityUser
	 */
	public function setCommunityUser($communityUser) {
		$this->communityUser = $communityUser;
	}

	/**
	 * @return string
	 */
	public function getPanelId() {
		return $this->panelId;
	}
	/**
	 * @param string $panelId
	 */
	public function setPanelId($panelId) {
		$this->panelId = $panelId;
	}

	/**
	 * @return string
	 */
	public function getCurrentState() {
		return $this->currentState;
	}

	/**
	 * @param string $currentState
	 */
	public function setCurrentState($currentState) {
		$this->currentState = $currentState;
	}

	/**
	 * @return NULL|Voting
	 */
	public function getNextVoting() {
		return $this->votingService->getNextVoting($this);
	}

	/**
	 * @return NULL|Voting
	 */
	public function getCurrentVoting() {
		return $this->votingService->getCurrentVoting($this->currentState);
	}

	/**
	 * Adds a PanelInvitation
	 *
	 * @param \Visol\EasyvoteEducation\Domain\Model\PanelInvitation $panelInvitation
	 * @return void
	 */
	public function addPanelInvitation(\Visol\EasyvoteEducation\Domain\Model\PanelInvitation $panelInvitation) {
		$this->panelInvitations->attach($panelInvitation);
	}

	/**
	 * Removes a PanelInvitation
	 *
	 * @param \Visol\EasyvoteEducation\Domain\Model\PanelInvitation $panelInvitationToRemove The PanelInvitation to be removed
	 * @return void
	 */
	public function removePanelInvitation(\Visol\EasyvoteEducation\Domain\Model\PanelInvitation $panelInvitationToRemove) {
		$this->panelInvitations->detach($panelInvitationToRemove);
	}

	/**
	 * Returns the panelInvitations
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Visol\EasyvoteEducation\Domain\Model\PanelInvitation> $panelInvitations
	 */
	public function getPanelInvitations() {
		return $this->panelInvitations;
	}

	/**
	 * Sets the panelInvitation
	 *
	 * @param $panelInvitations \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Visol\EasyvoteEducation\Domain\Model\PanelInvitation>
	 * @return void
	 */
	public function setPanelInvitations(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $panelInvitations) {
		$this->panelInvitations = $panelInvitations;
	}

	/**
	 * @return boolean
	 */
	public function isPanelInvitationsSent() {
		return $this->panelInvitationsSent;
	}

	/**
	 * @param boolean $panelInvitationsSent
	 */
	public function setPanelInvitationsSent($panelInvitationsSent) {
		$this->panelInvitationsSent = $panelInvitationsSent;
	}

	/**
	 * @return boolean
	 */
	public function isFeedbackMailSent() {
		return $this->feedbackMailSent;
	}

	/**
	 * @param boolean $feedbackMailSent
	 */
	public function setFeedbackMailSent($feedbackMailSent) {
		$this->feedbackMailSent = $feedbackMailSent;
	}

	/**
	 * @return boolean
	 */
	public function isReminderOnemonthSent() {
		return $this->reminderOnemonthSent;
	}

	/**
	 * @param boolean $reminderOnemonthSent
	 */
	public function setReminderOnemonthSent($reminderOnemonthSent) {
		$this->reminderOnemonthSent = $reminderOnemonthSent;
	}

	/**
	 * @return boolean
	 */
	public function isReminderOneweekSent() {
		return $this->reminderOneweekSent;
	}

	/**
	 * @param boolean $reminderOneweekSent
	 */
	public function setReminderOneweekSent($reminderOneweekSent) {
		$this->reminderOneweekSent = $reminderOneweekSent;
	}

	/**
	 * @return boolean
	 */
	public function isReminderTwoweeksSent() {
		return $this->reminderTwoweeksSent;
	}

	/**
	 * @param boolean $reminderTwoweeksSent
	 */
	public function setReminderTwoweeksSent($reminderTwoweeksSent) {
		$this->reminderTwoweeksSent = $reminderTwoweeksSent;
	}

	/**
	 * @return int
	 */
	public function getNumberOfAllowedPanelInvitations() {
		// minimum 2
		$numberOfParticipants = 2;
		if ($this->numberOfParticipants > 29) {
			$numberOfParticipants = 4;
		}
		if ($this->numberOfParticipants > 75) {
			$numberOfParticipants = 6;
		}
		return $numberOfParticipants;
	}

	/**
	 * @return boolean
	 */
	public function isPanelInvitationAllowed() {
		return $this->panelService->isPanelInvitationAllowedForPanel($this);
	}

	/**
	 * @return boolean
	 */
	public function isAllPanelInvitationsAccepted() {
		return $this->panelService->areAllPanelInvitationsAccepted($this);
	}

}